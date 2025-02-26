import 'package:boz/pages/navigation_page.dart';
import 'package:boz/pages/seller_navigation_page.dart';
import 'package:flutter/material.dart';
import 'pages/login_page.dart';
import 'pages/register_page.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';

Future main() async {
  //await dotenv.load(fileName: ".env");
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BOZ',
      theme: ThemeData(
        primarySwatch: Colors.blue,
      ),
      home: LoginPage(),
      initialRoute: '/home',
      routes: {
        '/login': (context) => LoginPage(),
        '/register': (context) => RegisterPage(),
        '/home': (context) => MyNavigation(),
        '/seller': (context) => SellerNavigation(),
      },
    );
  }
}