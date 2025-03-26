import 'package:boz/pages/navigation_page.dart';
import 'package:boz/pages/seller_navigation_page.dart';
import 'package:flutter/material.dart';
import 'pages/login_page.dart';
import 'pages/register_page.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:boz/services/remote_service.dart';

Future main() async {
  //await dotenv.load(fileName: ".env");
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  late Future<Widget> _initialPage;

  @override
  void initState() {
    super.initState();
    _initialPage = _determineInitialPage();
  }

  Future<Widget> _determineInitialPage() async {
    try {
      // Vérifier si l'utilisateur est connecté
      int? role = await RemoteService().getRole();

      switch (role) {
        case 1:
          return MyNavigation();
        case 2:
          return SellerNavigation();
        default:
          return LoginPage();
      }
    } catch (e) {
      // En cas d'erreur (par exemple, non connecté), renvoyer à la page de connexion
      return LoginPage();
    }
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BOZ',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: FutureBuilder<Widget>(
        future: _initialPage,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            // Afficher un écran de chargement pendant la vérification
            return const Scaffold(
              body: Center(
                child: CircularProgressIndicator(),
              ),
            );
          }

          if (snapshot.hasError) {
            // En cas d'erreur, renvoyer à la page de connexion
            return LoginPage();
          }

          return snapshot.data ?? LoginPage();
        },
      ),
      routes: {
        '/login': (context) => LoginPage(),
        '/register': (context) => RegisterPage(),
        '/home': (context) => MyNavigation(),
        '/seller': (context) => SellerNavigation(),
      },
    );
  }
}
