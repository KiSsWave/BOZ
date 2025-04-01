import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';
import 'package:boz/models/transaction.dart';
import 'package:boz/services/preferences.dart';

class HomePage extends StatefulWidget {
  const HomePage({Key? key}) : super(key: key);

  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  double balance = 0.0;
  late String lastUpdateFormatted = "01/01/1970";
  List<Transaction> allTransactions = [];
  List<Transaction> displayedTransactions = [];
  bool isLoading = false;
  int? role;

  final Preferences _preferences = Preferences();
  final RemoteService _remoteService = RemoteService();

  @override
  void initState() {
    super.initState();
    _initializeData();
  }

  Future<void> _initializeData() async {
    // Récupérer le rôle de l'utilisateur
    role = await _remoteService.getRole();

    await _initializePreferences();
  }

  Future<void> _initializePreferences() async {
    await _preferences.init();
    setState(() {
      balance = _preferences.getBalance();
      lastUpdateFormatted = _preferences.getLastUpdate();
      allTransactions = _preferences.getTransactions();
    });

    await _loadTransactions();
    await _fetchBalance();
  }

  Future<void> _loadTransactions() async {
    if (isLoading) return;

    setState(() {
      isLoading = true;
    });

    try {
      final response = await _remoteService.fetchHistory();
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final historyList = (data['transactions'] as List);

        setState(() {
          allTransactions =
              historyList.map((item) => Transaction.fromJson(item)).toList();
        });

        await _preferences.setTransactions(allTransactions);
        _filterTransactions(true);
      } else if (response.statusCode == 401) {
        await _remoteService.disconnectUser();
      } else {
        print('Error loading transactions: ${response.statusCode}');
      }
    } catch (e) {
      print('Error loading transactions: $e');
      setState(() {
        allTransactions = _preferences.getTransactions();
      });
    } finally {
      setState(() {
        isLoading = false;
      });
    }
  }

  Future<void> _fetchBalance() async {
    try {
      final response = await _remoteService.fetchBalance();
      final data = jsonDecode(response.body);
      final now = DateTime.now();
      final formattedDate = "${now.day}/${now.month}/${now.year}";

      setState(() {
        balance = data['balance'].toDouble();
        lastUpdateFormatted = formattedDate;
      });

      await _preferences.setBalance(balance);
      await _preferences.setLastUpdate(formattedDate);
    } catch (e) {
      print('Error fetching balance: $e');
    }
  }

  void _filterTransactions(bool? isRecharge) {
    setState(() {
      if (isRecharge == null) {
        displayedTransactions = allTransactions;
      } else if (isRecharge) {
        displayedTransactions =
            allTransactions.where((t) => t.isRecharge()).toList();
      } else {
        displayedTransactions =
            allTransactions.where((t) => t.isPayment()).toList();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    // Si le rôle n'est pas encore déterminé, afficher un indicateur de chargement
    if (role == null) {
      return const Center(child: CircularProgressIndicator());
    }

    // Déterminer quelle interface afficher en fonction du rôle
    if (role == 2) {
      // Interface du vendeur
      return _buildSellerHomePage();
    } else {
      // Interface de l'utilisateur standard (par défaut)
      return _buildUserHomePage();
    }
  }

  // Interface pour le vendeur
  Widget _buildSellerHomePage() {
    return SafeArea(
      child: RefreshIndicator(
        onRefresh: () async {
          await _fetchBalance();
        },
        color: Colors.black,
        backgroundColor: Colors.grey[300],
        child: Center(
          child: Column(
            children: [
              _buildBalanceCard(),
            ],
          ),
        ),
      ),
    );
  }

  // Interface pour l'utilisateur standard
  Widget _buildUserHomePage() {
    return SafeArea(
      child: RefreshIndicator(
        onRefresh: () async {
          await _loadTransactions();
          await _fetchBalance();
        },
        color: Colors.black,
        backgroundColor: Colors.grey[300],
        child: Column(
          children: [
            _buildBalanceCard(),
            const SizedBox(height: 20),
            _buildActionButtons(),
            const SizedBox(height: 20),
            Expanded(
              child: isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _buildTransactionList(),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBalanceCard() {
    return Container(
      margin: const EdgeInsets.only(top: 20),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.grey[300]!, width: 2)),
      ),
      child: Column(
        children: [
          Container(
            width: 200,
            height: 200,
            decoration: BoxDecoration(
              color: Colors.grey[800],
              borderRadius: BorderRadius.circular(50),
              boxShadow: const [
                BoxShadow(
                  color: Colors.black26,
                  blurRadius: 8,
                  offset: Offset(0, 4),
                ),
              ],
            ),
            child: Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    "${balance.toStringAsFixed(2)} €",
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    "Solde au $lastUpdateFormatted",
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
      children: [
        _buildActionButton(
          icon: Icons.history,
          label: "Rechargements",
          onPressed: () => _filterTransactions(true),
        ),
        _buildActionButton(
          icon: Icons.account_balance_wallet,
          label: "Transactions",
          onPressed: () => _filterTransactions(false),
        ),
      ],
    );
  }

  Widget _buildTransactionList() {
    return displayedTransactions.isNotEmpty
        ? ListView.builder(
            shrinkWrap: true,
            physics: const AlwaysScrollableScrollPhysics(),
            itemCount: displayedTransactions.length,
            itemBuilder: (context, index) {
              final transaction = displayedTransactions[index];
              return TransactionWidget(transaction: transaction);
            },
          )
        : const Center(
            child: Text(
              "Aucune transaction disponible",
              style: TextStyle(fontSize: 16),
            ),
          );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onPressed,
  }) {
    return ElevatedButton.icon(
      onPressed: onPressed,
      icon: Icon(icon, size: 24),
      label: Text(
        label,
        style: const TextStyle(fontSize: 16, color: Colors.black),
      ),
      style: ElevatedButton.styleFrom(
        iconColor: Colors.black,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
      ),
    );
  }
}
