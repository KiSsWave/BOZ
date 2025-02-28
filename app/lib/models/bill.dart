import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';

class Bill {
  final String label;
  final double amount;
  final String imageUrl;
  final String status;

  Bill({required this.label, required this.amount, required this.imageUrl, required this.status});

  factory Bill.fromJson(Map<String, dynamic> json) {
    return Bill(
      label: json['label'] ?? '',
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      imageUrl: json['qr_code'] ?? '',
      status: json['status'] ?? '',
    );
  }
}

class BillWidget extends StatelessWidget {
  final Bill bill;

  const BillWidget({Key? key, required this.bill}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final formattedAmount = "${bill.amount.abs().toStringAsFixed(2)}€";
    Uint8List? decodedImage;

    try {
      decodedImage = base64Decode(bill.imageUrl);
    } catch (e) {
      print("Error decoding QR code: $e");
    }

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.5),
            spreadRadius: 1,
            blurRadius: 2,
            offset: const Offset(0, 1),
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
                bill.label,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Text(
                formattedAmount,
                style: TextStyle(
                  fontSize: 18,
                  color: bill.amount < 0 ? Colors.red : Colors.green,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (decodedImage != null)
            Image.memory(decodedImage, height: 100, width: 100)
          else
            const Text("Invalid QR Code", style: TextStyle(color: Colors.red)),
          const SizedBox(height: 8),
          Text(
            bill.status,
            style: const TextStyle(
              fontSize: 16,
              color: Colors.grey,
            ),
          ),
        ],
      ),
    );
  }
}
