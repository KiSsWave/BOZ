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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.all(8.0),
              child: Text(
                "Mes Factures",
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
            ),
            Expanded(
              child: GridView.builder(
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 8,
                  mainAxisSpacing: 8,
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
        onPressed: () {},
        child: Icon(Icons.add),
      ),
    );
  }
}
