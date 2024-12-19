import 'package:flutter/material.dart';

class Transaction extends StatelessWidget {
  final String type;
  final double amount;

  const Transaction({Key? key, required this.type, required this.amount}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final isDebit = amount < 0;
    final formattedAmount = isDebit
        ? "-${amount.abs().toStringAsFixed(2)}€"
        : "+${amount.toStringAsFixed(2)}€";

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      decoration: BoxDecoration(
        border: Border.all(color: Colors.grey),
        borderRadius: BorderRadius.circular(12),
      ),
      child: ListTile(
        title: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              type,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
              ),
            ),
            Text(
              formattedAmount,
              style: TextStyle(
                color: isDebit ? Colors.red : Colors.green,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
