import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';

class QRPage extends StatefulWidget {
  const QRPage({super.key});

  @override
  State<QRPage> createState() => _QRPageState();
}

class _QRPageState extends State<QRPage> {

  @override
  void initState() {
    super.initState();
    scanQR();
  }

  Future<void> scanQR() async {
    String scanResult;
    try {
      scanResult = await FlutterBarcodeScanner.scanBarcode(
          "#ff6666", "Cancel", true, ScanMode.QR);
      print(scanResult);
    } on PlatformException {
      scanResult = "Failed to get platform version.";
    }
    if (!mounted) return;
  }

  @override
  Widget build(BuildContext context) {
    return const Placeholder();
  }
}
