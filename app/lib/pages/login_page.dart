import 'package:app/components/my_button.dart';
import 'package:app/components/my_textfield.dart';
import 'package:flutter/material.dart';

class LoginPage extends StatelessWidget {
  LoginPage({Key? key}) : super(key: key);

  final usernameController = TextEditingController();
  final passwordController = TextEditingController();

  void signUserIn() {
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        backgroundColor: Colors.grey[300],
        body: SafeArea(
            child: Column(
          children: [
            const SizedBox(
              height: 50,
            ),

            const Icon(
              Icons.lock,
              size: 100,
            ),

            const SizedBox(
              height: 50,
            ),

            Text(
              "Content de vous revoir !",
              style: TextStyle(color: Colors.grey[700]),
            ),

            const SizedBox(
              height: 25,
            ),

            MyTextField(
              controller: usernameController,
              hintText: "Nom d'utilisateur",
              obscureText: false,
            ),

            const SizedBox(
              height: 25,
            ),

            MyTextField(
              controller: passwordController,
              hintText: "Mot de passe",
              obscureText: true,
            ),

            const SizedBox(
              height: 25,
            ),

            MyButton(
              onTap: signUserIn,
            ),


          ],
        )));
  }
}
