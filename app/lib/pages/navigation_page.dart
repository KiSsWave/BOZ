import 'package:boz/pages/qr_page.dart';
import 'package:flutter/material.dart';
import 'home_page.dart';
import 'package:boz/services/remote_service.dart';

class MyNavigation extends StatefulWidget {
  MyNavigation({Key? key}) : super(key: key);

  @override
  State<MyNavigation> createState() => MyNavigationBar();
}

class MyNavigationBar extends State<MyNavigation> {
  int _currentIndex = 0;

  final List<Widget> body = [
    const HomePage(),
    const QRPage(), // Placeholder pour le scanner
    const Center(child: Text("Paramètres")), // Placeholder pour le profil
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

  void _showLogoutConfirmation(BuildContext context) {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text("Confirmation"),
          content: const Text("Êtes-vous sûr de vouloir vous déconnecter ?"),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(); // Fermer la boîte de dialogue
              },
              child: const Text("Annuler"),
            ),
            TextButton(
              onPressed: () {
                Navigator.of(context).pop(); // Fermer la boîte de dialogue
                _handleLogout(); // Exécuter la déconnexion
              },
              child: const Text(
                "Confirmer",
                style: TextStyle(color: Colors.red),
              ),
            ),
          ],
        );
      },
    );
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
      body: _currentIndex < body.length ? body[_currentIndex] : const SizedBox(),
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
            icon: Icon(Icons.qr_code),
            label: "Scanner",
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: "Profil",
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.logout),
            label: "Déconnexion",
          ),
        ],
        onTap: (index) {
          if (index == 3) {
            _showLogoutConfirmation(context);
          } else {
            setState(() {
              _currentIndex = index;
            });
          }
        },
      ),
    );
  }
}
