import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';
import 'package:boz/models/transaction.dart';
import 'package:boz/services/preferences.dart';

class SellerHomePage extends StatefulWidget {
  const SellerHomePage({Key? key}) : super(key: key);

  @override
  _SellerHomePageState createState() => _SellerHomePageState();
}

class _SellerHomePageState extends State<SellerHomePage> {
  double balance = 0.0;
  late String lastUpdateFormatted = "01/01/1970";
  List<Transaction> allTransactions = [];
  List<Transaction> displayedTransactions = [];
  bool isLoading = false;

  final Preferences _preferences = Preferences();
  final RemoteService _remoteService = RemoteService();

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
        final historyList = (data['history'] as List);

        setState(() {
          allTransactions =
              historyList.map((item) => Transaction.fromJson(item)).toList();
        });

        await _preferences.setTransactions(allTransactions);
        _filterTransactions(true);
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
    return SafeArea(
      child: RefreshIndicator(
        onRefresh: () async {
          await _fetchBalance();
        },
        color: Colors.black,
        backgroundColor: Colors.grey[300],
        child: Column(
          children: [
            _buildBalanceCard(),
          ],
        ),
      ),
    );
  }

  Widget _buildBalanceCard() {
    return Center(
      child: Container(
        margin: const EdgeInsets.only(top: 20),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          border:
              Border(bottom: BorderSide(color: Colors.grey[300]!, width: 2)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
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
