import 'package:flutter/material.dart';
import 'home_page.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class MyNavigation extends StatefulWidget {
  MyNavigation({Key? key}) : super(key: key);

  @override
  State<MyNavigation> createState() => MyNavigationBar();
}

class MyNavigationBar extends State<MyNavigation> {
  int _currentIndex = 0;

  // Liste des pages (y compris HomePage)
  final List<Widget> body = [
    const HomePage(), // Ajout de la page d'accueil
    const Icon(Icons.person), // Page Profil
    const Icon(Icons.settings), // Page Paramètres
    const Icon(Icons.logout), // Page Déconnexion
  ];

  void _handleLogout() async {
  // Instance de SecureStorage
  const storage = FlutterSecureStorage();
  // Supprimer le token JWT
  await storage.delete(key: "jwt_token");
  // Naviguer vers l'écran de connexion
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
      body: body[_currentIndex], // Affiche la page sélectionnée
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        type: BottomNavigationBarType
            .fixed, // Obligatoire pour appliquer un fond constant
        backgroundColor: Colors.black,
        selectedItemColor: Colors.white,
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home),
            label: "Accueil",
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: "Profil",
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.settings),
            label: "Paramètres",
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.logout),
            label: "Déconnexion",
          ),
        ],
        onTap: (index) {
          if (index == 3) {
            // Affiche la boîte de dialogue de confirmation pour la déconnexion
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
