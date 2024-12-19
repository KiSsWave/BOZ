import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';
import 'package:boz/models/transaction.dart';

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

  @override
  void initState() {
    super.initState();
    _fetchBalance();
  }

  Future<void> _fetchBalance() async {
    try {
      final response = await RemoteService().fetchBalance();
      final data = jsonDecode(response.body);
      setState(() {
        balance = data;
        final now = DateTime.now();
        lastUpdateFormatted = "${now.day}/${now.month}/${now.year}";
      });
    } catch (e) {
      // Handle error gracefully
      print('Error fetching balance: $e');
    }
  }

  List<Transaction> _filterTransactions(bool isRecharge) {
    displayedTransactions = isRecharge ? allTransactions.where((t) => t.amount > 0).toList() : allTransactions.where((t) => t.amount < 0).toList();
    return displayedTransactions;
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: RefreshIndicator(
        onRefresh: _fetchBalance,
        color: Colors.black,
        backgroundColor: Colors.grey[300],
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: Column(
            children: [
              Container(
                width: 200,
                height: 200,
                margin: const EdgeInsets.only(top: 20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border.all(color: Colors.black),
                  borderRadius: BorderRadius.circular(24),
                ),
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        "$balance €",
                        style: const TextStyle(
                          fontSize: 30,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Text(
                        "Solde au $lastUpdateFormatted",
                        style: const TextStyle(
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildActionButton(
                    icon: Icons.history,
                    label: "Rechargements",
                    onPressed: () => print("Rechargements"),
                  ),
                  _buildActionButton(
                    icon: Icons.account_balance_wallet,
                    label: "Transactions",
                    onPressed: () => print("Transactions"),
                  ),
                ],
              ),
            ],
          ),
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
