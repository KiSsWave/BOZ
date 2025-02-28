import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'check_bill.dart'; // Assure-toi d'importer la page CheckBill

class QRPage extends StatefulWidget {
  const QRPage({super.key});

  @override
  State<QRPage> createState() => _QRPageState();
}

class _QRPageState extends State<QRPage> {
  final MobileScannerController _controller = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    returnImage: true,
  );

  /// Fonction pour afficher un dialogue de chargement
  void showLoadingDialog() {
    showDialog(
      context: context,
      barrierDismissible: false, // Empêche la fermeture du dialogue
      builder: (context) {
        return AlertDialog(
          content: Row(
            children: [
              const CircularProgressIndicator(),
              const SizedBox(width: 20),
              const Text("Vérification..."),
            ],
          ),
        );
      },
    );
  }

  /// Fonction de traitement après scan
  void _onDetect(Barcode barcode) async {
    if (barcode.rawValue == null) return;

    final String qrData = barcode.rawValue!;
    print("QR Code Scanné: $qrData");

    // Affiche le dialogue de chargement
    showLoadingDialog();

    // Vérifie si c'est un lien externe
    if (Uri.tryParse(qrData)?.isAbsolute ?? false) {
      Navigator.pop(context); // Ferme le dialogue de chargement
      _showErrorDialog("Ce QR Code ne correspond pas à une facture.");
      return;
    }

    // Simule un appel au service distant (remplace ça par ton API réelle)
    await Future.delayed(const Duration(seconds: 2));

    // Ferme le dialogue de chargement
    if (mounted) Navigator.pop(context);

    // Redirige vers la page de vérification de facture
    if (mounted) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => CheckBillPage(qrData: qrData),
        ),
      );
    }
  }

  /// Affiche une alerte d'erreur
  void _showErrorDialog(String message) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Erreur"),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("OK"),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: MobileScanner(
        controller: _controller,
        onDetect: (capture) {
          final List<Barcode> barcodes = capture.barcodes;
          for (final barcode in barcodes) {
            _controller.stop(); // Stoppe le scanner après lecture
            _onDetect(barcode);
          }
        },
      ),
    );
  }
}
