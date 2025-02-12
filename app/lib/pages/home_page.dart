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
  late String lastUpdateFormatted = "";
  List<Transaction> allTransactions = [];
  List<Transaction> displayedTransactions = [];

  final Preferences _preferences = Preferences();

  @override
  void initState() {
    super.initState();
    _initializePreferences();
  }

  Future<void> _initializePreferences() async {
    await _preferences.init();

    setState(() {
      balance = _preferences.getBalance();
      lastUpdateFormatted = _preferences.getLastUpdate();
      allTransactions = _preferences.getTransactions();
    });

    if (allTransactions.isEmpty) {
      await _generateMockTransactions();
    }

    _filterTransactions(true); // Par défaut, afficher les rechargements
    await _fetchBalance();
  }

  Future<void> _generateMockTransactions() async {
    final mockTransactions = List.generate(
      10,
      (index) => Transaction(
        type: index % 2 == 0 ? "Recharge" : "Achat",
        amount: index % 2 == 0 ? (20 + index).toDouble() : -(15 + index).toDouble(),
      ),
    );

    setState(() {
      allTransactions = mockTransactions;
    });

    await _preferences.setTransactions(mockTransactions);
  }

  Future<void> _fetchBalance() async {
    try {
      final response = await RemoteService().fetchBalance();
      final data = jsonDecode(response.body);
      final now = DateTime.now();
      final formattedDate = "${now.day}/${now.month}/${now.year}";

      setState(() {
        balance = data['balance'];
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
        displayedTransactions = allTransactions; // Affiche toutes les transactions
      } else if (isRecharge) {
        displayedTransactions = allTransactions.where((t) => t.amount > 0).toList();
      } else {
        displayedTransactions = allTransactions.where((t) => t.amount < 0).toList();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: RefreshIndicator(
        onRefresh: () async {
          await _fetchBalance();
          _filterTransactions(null);
        },
        color: Colors.black,
        backgroundColor: Colors.grey[300],
        child: Column(
          children: [
            _buildBalanceCard(),
            const SizedBox(height: 20),
            _buildActionButtons(),
            const SizedBox(height: 20),
            // Utilisation de Expanded pour faire en sorte que la ListView occupe l'espace restant
            Expanded(
              child: _buildTransactionList(),
            ),
          ],
        ),
      ),
    );
  }

  // Carte avec solde et date dans un cercle
  Widget _buildBalanceCard() {
    return Container(
      margin: const EdgeInsets.only(top: 20),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.grey[300]!, width: 2)), // Bottom border
      ),
      child: Column(
        children: [
          // Cercle contenant le solde et la date
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
                    "$balance €",
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
            physics: const AlwaysScrollableScrollPhysics(), // Permet de rafraîchir même sans défilement
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
