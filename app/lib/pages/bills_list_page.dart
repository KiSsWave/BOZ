import 'package:flutter/material.dart';
import 'package:boz/models/bill.dart';
import 'package:boz/services/remote_service.dart';
import 'dart:convert';
import 'package:boz/pages/bill_details_page.dart';
import 'add_bill_page.dart';

class BillsListPage extends StatefulWidget {
  const BillsListPage({Key? key}) : super(key: key);

  @override
  _BillsListPageState createState() => _BillsListPageState();
}

class _BillsListPageState extends State<BillsListPage> with SingleTickerProviderStateMixin {
  final RemoteService _remoteService = RemoteService();
  List<Bill> bills = [];
  bool isLoading = true;
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _fetchBills();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchBills() async {
    setState(() {
      isLoading = true;
    });

    try {
      final response = await _remoteService.fetchBills();
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body)["factures"] as List;
        setState(() {
          bills = data.map((item) => Bill.fromJson(item)).toList();
          isLoading = false;
        });
      } else {
        setState(() {
          isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Erreur lors du chargement des factures')),
        );
      }
    } catch (e) {
      setState(() {
        isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Erreur: $e')),
      );
      print("Error fetching bills: $e");
    }
  }

  void _navigateToAddBill() async {
    final result = await Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const AddBillPage()),
    );

    if (result == true) {
      _fetchBills();
    }
  }

  void _openBillDetails(Bill bill) {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => BillDetailPage(bill: bill)),
    );
  }

  List<Bill> get _unpaidBills => bills.where((bill) => bill.status == "non payée").toList();
  List<Bill> get _paidBills => bills.where((bill) => bill.status == "payée").toList();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Mes Factures"),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Non réglées'),
            Tab(text: 'Réglées'),
          ],
        ),
        actions: [], // Explicitement définir actions comme une liste vide
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          RefreshIndicator(
            onRefresh: _fetchBills,
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : _unpaidBills.isEmpty
                    ? const Center(child: Text("Aucune facture non réglée"))
                    : ListView.separated(
                        padding: const EdgeInsets.all(16.0),
                        itemCount: _unpaidBills.length,
                        separatorBuilder: (context, index) => const Divider(),
                        itemBuilder: (context, index) {
                          final bill = _unpaidBills[index];
                          return ListTile(
                            title: Text(bill.libelle),
                            subtitle: Text(
                                'Montant: ${bill.montant.toStringAsFixed(2)} €'),
                            trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                            onTap: () => _openBillDetails(bill),
                          );
                        },
                      ),
          ),
          RefreshIndicator(
            onRefresh: _fetchBills,
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : _paidBills.isEmpty
                    ? const Center(child: Text("Aucune facture réglée"))
                    : ListView.separated(
                        padding: const EdgeInsets.all(16.0),
                        itemCount: _paidBills.length,
                        separatorBuilder: (context, index) => const Divider(),
                        itemBuilder: (context, index) {
                          final bill = _paidBills[index];
                          return ListTile(
                            title: Text(bill.libelle),
                            subtitle: Text(
                                'Montant: ${bill.montant.toStringAsFixed(2)} €'),
                            trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                            onTap: () => _openBillDetails(bill),
                          );
                        },
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _navigateToAddBill,
        child: const Icon(Icons.add),
        tooltip: 'Ajouter une facture',
      ),
    );
  }
}
