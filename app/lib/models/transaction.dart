import 'package:flutter/material.dart';

class Transaction {
  final String transaction_id;
  final String type;
  final double amount;
  final String timestamp;

  const Transaction({
    required this.transaction_id,
    required this.type,
    required this.amount,
    required this.timestamp,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      transaction_id: json['transaction_id'] ?? '',
      type: json['type'] ?? '',
      amount: double.parse(json['amount'].toString()),
      timestamp: json['timestamp'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'transaction_id': transaction_id,
      'type': type,
      'amount': amount,
      'timestamp': timestamp,
    };
  }

  bool isRecharge() => type == 'add';
  bool isPayment() => type == 'pay';
}

class TransactionWidget extends StatelessWidget {
  final Transaction transaction;

  const TransactionWidget({Key? key, required this.transaction}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final isPayment = transaction.isPayment();
    final formattedAmount = isPayment
        ? "-${transaction.amount.abs().toStringAsFixed(2)}€"
        : "+${transaction.amount.toStringAsFixed(2)}€";

    DateTime? date;
    String formattedDate = "";
    try {
      date = DateTime.parse(transaction.timestamp);
      formattedDate = "${date.day}/${date.month}/${date.year}";
    } catch (e) {
      formattedDate = "Date non disponible";
    }

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.black12,
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                transaction.isRecharge() ? "Rechargement" : "Paiement",
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                formattedAmount,
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isPayment ? Colors.red : Colors.green,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            formattedDate,
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }
}