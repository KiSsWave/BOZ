import 'package:flutter/material.dart';
import 'package:boz/models/bill.dart';
import 'package:boz/services/remote_service.dart';
import 'dart:convert';

class BillsPage extends StatefulWidget {
  const BillsPage({Key? key}) : super(key: key);

  @override
  _BillsPageState createState() => _BillsPageState();
}

class _BillsPageState extends State<BillsPage> {
  final RemoteService _remoteService = RemoteService();
  List<Bill> bills = [];
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _libelleController = TextEditingController();
  final TextEditingController _montantController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _fetchBills();
  }

  Future<void> _fetchBills() async {
    try {
      final response = await _remoteService.fetchBills();
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body)["factures"] as List;
        setState(() {
          bills = data.map((item) => Bill.fromJson(item)).toList();
        });
      }
    } catch (e) {
      print("Error fetching bills: \$e");
    }
  }

  void _showAddBillDialog() {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: const Text('Ajouter une Facture'),
          content: Form(
            key: _formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextFormField(
                  controller: _libelleController,
                  decoration: const InputDecoration(labelText: 'Libellé'),
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Le libellé est obligatoire';
                    }
                    return null;
                  },
                ),
                TextFormField(
                  controller: _montantController,
                  decoration: const InputDecoration(labelText: 'Montant'),
                  keyboardType: TextInputType.number,
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      return 'Le montant est obligatoire';
                    }
                    if (double.tryParse(value) == null) {
                      return 'Veuillez entrer un montant valide';
                    }
                    return null;
                  },
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(); // Ferme le dialogue
              },
              child: const Text('Annuler'),
            ),
            TextButton(
              onPressed: () {
                if (_formKey.currentState?.validate() ?? false) {
                  RemoteService().createBill(
                    _libelleController.text,
                    double.parse(_montantController.text),
                  );
                  _fetchBills();
                  Navigator.of(context).pop(); // Ferme le dialogue
                }
              },
              child: const Text('Ajouter'),
            ),
          ],
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Padding(
              padding: EdgeInsets.all(8.0),
              child: Text(
                "Mes Factures",
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
            ),
            Expanded(
              child: GridView.builder(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 4,
                  mainAxisSpacing: 4,
                  childAspectRatio: 0.8,
                ),
                itemCount: bills.length,
                itemBuilder: (context, index) {
                  return BillWidget(bill: bills[index]);
                },
              ),
            ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddBillDialog, // Ouvre le dialogue pour ajouter une facture
        child: const Icon(Icons.add),
      ),
    );
  }
}
