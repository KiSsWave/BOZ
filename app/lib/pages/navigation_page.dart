import 'package:boz/pages/help_page.dart';
import 'package:boz/pages/settings_page.dart';
import 'package:boz/pages/qr_page.dart';
import 'package:boz/pages/bills_list_page.dart';
import 'package:flutter/material.dart';
import 'home_page.dart';
import 'package:boz/services/remote_service.dart';

class NavigationPage extends StatefulWidget {
  const NavigationPage({Key? key}) : super(key: key);

  @override
  State<NavigationPage> createState() => _NavigationPageState();
}

class _NavigationPageState extends State<NavigationPage> {
  int _currentIndex = 0;
  int? role;
  late List<Widget> userPages;
  late List<Widget> sellerPages;
  bool isLoading = true;

  @override
  void initState() {
    super.initState();
    // Initialiser les listes de pages pour chaque rôle
    userPages = [
      const HomePage(),
      const QRPage(),
      const BillsListPage(),
      const HelpPage(),
      const SettingsPage()
    ];

    sellerPages = [
      const HomePage(),
      const BillsListPage(),
      const HelpPage(),
      const SettingsPage()
    ];

    _checkConnection();
    _fetchUserRole();
  }

  Future<void> _fetchUserRole() async {
    try {
      role = await RemoteService().getRole();
      setState(() {
        isLoading = false;
      });
    } catch (e) {
      print("Error fetching user role: $e");
      setState(() {
        isLoading = false;
      });
    }
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

  List<BottomNavigationBarItem> _getUserNavigationItems() {
    return const [
      BottomNavigationBarItem(
        icon: Icon(Icons.home),
        label: "Accueil",
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.qr_code),
        label: "Scanner",
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.description),
        label: "Factures",
      ),
      BottomNavigationBarItem(icon: Icon(Icons.help), label: "Aide"),
      BottomNavigationBarItem(
        icon: Icon(Icons.settings),
        label: "Paramètres",
      ),
    ];
  }

  List<BottomNavigationBarItem> _getSellerNavigationItems() {
    return const [
      BottomNavigationBarItem(
        icon: Icon(Icons.home),
        label: "Accueil",
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.description),
        label: "Factures",
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.help),
        label: "Aide",
      ),
      BottomNavigationBarItem(
        icon: Icon(Icons.settings),
        label: "Paramètres",
      ),
    ];
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }

    // Déterminer quelles pages et éléments de navigation afficher en fonction du rôle
    final currentPages = (role == 2) ? sellerPages : userPages;
    final navigationItems =
        (role == 2) ? _getSellerNavigationItems() : _getUserNavigationItems();

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
            "assets/logoBOZ.png",
            fit: BoxFit.contain,
          ),
        ),
      ),
      backgroundColor: Colors.grey[300],
      body: _currentIndex < currentPages.length
          ? currentPages[_currentIndex]
          : const SizedBox(),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        type: BottomNavigationBarType.fixed,
        backgroundColor: Colors.black,
        selectedItemColor: Colors.white,
        unselectedItemColor: Colors.grey,
        items: navigationItems,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
    );
  }
}
