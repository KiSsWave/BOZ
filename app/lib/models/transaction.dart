import 'package:flutter/material.dart';

class Transaction {
  final String type;
  final double amount;
  const Transaction({required this.type, required this.amount});

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      type: json['type'],
      amount: json['amount'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'type': type,
      'amount': amount,
    };
  }
}

class TransactionWidget extends StatelessWidget {
  final Transaction transaction;

  const TransactionWidget({Key? key, required this.transaction}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final isDebit = transaction.amount < 0;
    final formattedAmount = isDebit
        ? "-${transaction.amount.abs().toStringAsFixed(2)}€"
        : "+${transaction.amount.toStringAsFixed(2)}€";

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: Colors.grey[300]!), // Légère bordure
        borderRadius: BorderRadius.circular(8), // Coins arrondis
        boxShadow: [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 8,
            offset: const Offset(0, 4), // Ombre légère sous chaque élément
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Type de transaction
          Text(
            transaction.type,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: Colors.black,
              fontFamily: 'Courier', // Police type "monospace" comme un reçu
            ),
          ),
          // Montant de la transaction
          Text(
            formattedAmount,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: isDebit ? Colors.red : Colors.green,
              fontFamily: 'Courier', // Police type "monospace" comme un reçu
            ),
          ),
        ],
      ),
    );
  }
}
