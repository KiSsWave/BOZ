import 'package:app/components/my_button.dart';
import 'package:app/components/my_textfield.dart';
import 'package:flutter/material.dart';

class RegisterPage extends StatelessWidget {
  RegisterPage({Key? key}) : super(key: key);

  final usernameController = TextEditingController();
  final passwordController = TextEditingController();
  final confirmPasswordController = TextEditingController();
  final firstNameController = TextEditingController();
  final lastNameController = TextEditingController();

  void registerUserIn() {}

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[300],
      body: SafeArea(
        child: SingleChildScrollView(
          child: Column(
            children: [
              const SizedBox(
                height: 50,
              ),
              ClipRRect(
                borderRadius:
                    BorderRadius.circular(20), // Rayon des bords arrondis
                child: const Image(
                  image: AssetImage("logoBOZ.png"),
                  height: 100,
                  width: 100,
                  fit: BoxFit
                      .cover, // Ajuste l'image pour qu'elle remplisse l'espace défini
                ),
              ),
              const SizedBox(
                height: 50,
              ),
              Text(
                "Bienvenue sur BOZ !",
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
                controller: firstNameController,
                hintText: "Prénom",
                obscureText: false,
              ),
              const SizedBox(
                height: 25,
              ),
              MyTextField(
                controller: lastNameController,
                hintText: "Nom",
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
              MyTextField(
                controller: confirmPasswordController,
                hintText: "Confirmer le mot de passe",
                obscureText: true,
              ),
              const SizedBox(
                height: 25,
              ),
              MyButton(
                onTap: registerUserIn,
                text: "S'inscrire",
              ),
              const SizedBox(
                height: 25,
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text("Déjà un compte ?"),
                  TextButton(
                    onPressed: () {
                      Navigator.pushNamed(context, "/login");
                    },
                    child: const Text("Se Connecter"),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
