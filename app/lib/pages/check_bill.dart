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
      String message = success ? 'Achat confirmé' : 'Erreur lors de la confirmation';

      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
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
        appBar: AppBar(title: const Text('Erreur')),
        body: const Center(child: Text('Impossible de charger la facture')),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Check Bill')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildBillInfo('Tarif', '${billData["tarif"]}€'),
            _buildBillInfo('Label', billData["label"]),
            _buildBillInfo('Vendeur', billData["seller"]),
            const SizedBox(height: 20),
            _buildActionButtons(),
          ],
        ),
      ),
    );
  }

  Widget _buildBillInfo(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8.0),
      child: Text('$label: $value', style: const TextStyle(fontSize: 16)),
    );
  }

  Widget _buildActionButtons() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
      children: [
        ElevatedButton(
          onPressed: isLoading ? null : () => Navigator.pop(context),
          child: const Text('Annuler'),
        ),
        ElevatedButton(
          onPressed: isLoading ? null : _confirmPayment,
          style: ElevatedButton.styleFrom(backgroundColor: Colors.blue),
          child: isLoading
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white))
              : const Text('Confirmer', style: TextStyle(color: Colors.white)),
        ),
      ],
    );
  }
}
