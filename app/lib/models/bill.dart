import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';

class Bill {
  final String label;
  final double amount;
  final String imageUrl;
  final String status;

  Bill({
    required this.label,
    required this.amount,
    required this.imageUrl,
    required this.status,
  });

  factory Bill.fromJson(Map<String, dynamic> json) {
    return Bill(
      label: json['label'] ?? '',
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      imageUrl: json['qr_code'] ?? '',
      status: json['status'] ?? '',
    );
  }

  /// Décode le QR code en `Uint8List`
  Uint8List? getDecodedQrCode() {
    try {
      return base64Decode(imageUrl);
    } catch (e) {
      print("Error decoding QR code: $e");
      return null;
    }
  }
}

class BillWidget extends StatelessWidget {
  final Bill bill;

  const BillWidget({Key? key, required this.bill}) : super(key: key);

  /// Affiche le QR code en grand dans un `Dialog`
  void showQrCodeDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(20.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Hero(
                  tag: 'qr-${bill.label}',
                  child: Image.memory(
                    bill.getDecodedQrCode()!,
                    width: MediaQuery.of(context).size.width * 0.8,
                    height: MediaQuery.of(context).size.width * 0.8,
                    fit: BoxFit.contain,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  bill.label,
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 10),
                ElevatedButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text("Fermer"),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final formattedAmount = "${bill.amount.abs().toStringAsFixed(2)}€";
    final Uint8List? decodedImage = bill.getDecodedQrCode();
    final double qrSize = MediaQuery.of(context).size.width * 0.65; // Réduction à 65% pour éviter tout dépassement

    return GestureDetector(
      onTap: decodedImage != null ? () => showQrCodeDialog(context) : null,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(8),
          boxShadow: [
            BoxShadow(
              color: Colors.grey.withOpacity(0.3),
              spreadRadius: 1,
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min, // Empêche l'extension inutile
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    bill.label,
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    overflow: TextOverflow.ellipsis,
                    maxLines: 1,
                  ),
                ),
                const SizedBox(width: 10),
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
              Hero(
                tag: 'qr-${bill.label}',
                child: Container(
                  width: qrSize,
                  child: AspectRatio(
                    aspectRatio: 1, // ✅ Garde un carré parfait pour éviter tout dépassement
                    child: Image.memory(decodedImage, fit: BoxFit.contain),
                  ),
                ),
              )
            else
              const Text("QR Code invalide", style: TextStyle(color: Colors.red)),
            const Spacer(), // ✅ Pousse le statut vers le bas pour éviter les chevauchements
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Text(
                bill.status,
                style: const TextStyle(fontSize: 16, color: Colors.grey),
                textAlign: TextAlign.center,
                softWrap: true,
              ),
            ),
          ],
        ),
      ),
    );
  }
}