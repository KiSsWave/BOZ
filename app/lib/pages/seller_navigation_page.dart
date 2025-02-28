import 'package:boz/pages/qr_page.dart';
import 'package:flutter/material.dart';
import 'seller_home_page.dart';
import 'settings_page.dart';
import 'bills.dart';
import 'package:boz/services/remote_service.dart';

class SellerNavigation extends StatefulWidget {
  SellerNavigation({Key? key}) : super(key: key);

  @override
  State<SellerNavigation> createState() => SellerNavigationBar();
}

class SellerNavigationBar extends State<SellerNavigation> {
  int _currentIndex = 0;

  final List<Widget> body = [
    const SellerHomePage(),
    const BillsPage(),
    const SettingsPage(),
  ];

  @override
  void initState() {
    super.initState();
    _checkConnection();
  }

  Future<void> _checkConnection() async {
    final isConnected = await RemoteService().isConnected();
    if (!isConnected) {
      Navigator.pushReplacementNamed(context, "/login");
    }
  }

  void _handleLogout() async {
    await RemoteService().disconnectUser();
    Navigator.pushReplacementNamed(context, "/login");
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          "BOZ",
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        automaticallyImplyLeading: false,
        centerTitle: true,
        backgroundColor: Colors.black,
        leading: Padding(
          padding: const EdgeInsets.all(8.0),
          child: Image.asset(
            "logoBOZ.png",
            fit: BoxFit.contain,
          ),
        ),
      ),
      backgroundColor: Colors.grey[300],
      body:
          _currentIndex < body.length ? body[_currentIndex] : const SizedBox(),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        type: BottomNavigationBarType.fixed, // Fond constant
        backgroundColor: Colors.black,
        selectedItemColor: Colors.white,
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: "Accueil",
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.description),
            label: "Factures",
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.settings),
            label: "Paramètres",
          ),
        ],
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
    );
  }
}
