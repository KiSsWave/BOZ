import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:boz/services/remote_service.dart';

class CheckBillPage extends StatefulWidget {
  final String qrData;

  const CheckBillPage({Key? key, required this.qrData}) : super(key: key);

  @override
  _CheckBillPageState createState() => _CheckBillPageState();
}

class _CheckBillPageState extends State<CheckBillPage> {
  late Map<String, dynamic> billData;
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    try {
      billData = jsonDecode(widget.qrData);
    } catch (e) {
      billData = {};
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Erreur de lecture des données')),
      );
    }
  }

  Future<void> _confirmPayment() async {
    setState(() => isLoading = true);
    try {
      final response = await RemoteService().payBill(billData["id"]);
      bool success = response.statusCode == 200;
      String message =
          success ? 'Achat confirmé' : 'Erreur lors de la confirmation';

      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(message)));
      if (success) Navigator.pop(context);
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Une erreur est survenue : $e')),
      );
    } finally {
      setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (billData.isEmpty) {
      return Scaffold(
        backgroundColor: Colors.white,
        appBar: AppBar(
          title: const Text('Erreur', style: TextStyle(color: Colors.black)),
          backgroundColor: Colors.white,
          iconTheme: const IconThemeData(color: Colors.black),
        ),
        body: const Center(
          child: Text(
            'Impossible de charger la facture',
            style: TextStyle(color: Colors.black, fontSize: 16),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        title:
            const Text('Régler la facture', style: TextStyle(color: Colors.white)),
        backgroundColor: Colors.black,
        iconTheme: const IconThemeData(color: Colors.white),
        elevation: 0,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.black),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Informations',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.black,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Table(
                    border: TableBorder.all(color: Colors.black),
                    columnWidths: const {
                      0: FlexColumnWidth(1),
                      1: FlexColumnWidth(2),
                    },
                    children: [
                      _buildTableRow('Tarif', '${billData["tarif"]}€'),
                      _buildTableRow('Label', billData["label"] ?? '-'),
                      _buildTableRow('Vendeur', billData["seller"] ?? '-'),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            _buildActionButtons(),
          ],
        ),
      ),
    );
  }

  TableRow _buildTableRow(String label, String value) {
    return TableRow(
      children: [
        Padding(
          padding: const EdgeInsets.all(8.0),
          child: Text(
            label,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
          ),
        ),
        Padding(
          padding: const EdgeInsets.all(8.0),
          child: Text(
            value,
            style: const TextStyle(fontSize: 16),
          ),
        ),
      ],
    );
  }

  Widget _buildActionButtons() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
      children: [
        ElevatedButton(
          onPressed: isLoading ? null : () => Navigator.pop(context),
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.grey[300],
            foregroundColor: Colors.black,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          ),
          child: const Text('Annuler', style: TextStyle(fontSize: 16)),
        ),
        ElevatedButton(
          onPressed: isLoading ? null : _confirmPayment,
          style: ElevatedButton.styleFrom(
            backgroundColor: Colors.black,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
          ),
          child: isLoading
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(color: Colors.white))
              : const Text('Confirmer', style: TextStyle(fontSize: 16)),
        ),
      ],
    );
  }
}
