import 'package:boz/components/my_button.dart';
import 'package:boz/components/my_textfield.dart';
import 'package:boz/services/remote_service.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart';

class RegisterPage extends StatelessWidget {
  RegisterPage({Key? key}) : super(key: key);

  final usernameController = TextEditingController();
  final passwordController = TextEditingController();
  final emailController = TextEditingController();

  Future<Response> registerUser() async {
    final email = emailController.text;
    final username = usernameController.text;
    final password = passwordController.text;

    return await RemoteService().registerUser(email, username, password);
  }

  void _showSnackBar(BuildContext context, String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, style: const TextStyle(color: Colors.white)),
        backgroundColor: color,
      ),
    );
  }

  Widget _buildTextField(
      {required TextEditingController controller,
      required String hintText,
      required bool obscureText}) {
    return MyTextField(
      controller: controller,
      hintText: hintText,
      obscureText: obscureText,
    );
  }

  Widget _buildLogo() {
    return ClipRRect(
      borderRadius: BorderRadius.circular(20),
      child: const Image(
        image: AssetImage("assets/logoBOZ.png"),
        height: 100,
        width: 100,
        fit: BoxFit.cover,
      ),
    );
  }

  Widget _buildSignUpButton(BuildContext context) {
    return MyButton(
      onTap: () async {
        _showSnackBar(context, "Inscription en cours ...", Colors.black);

        final response = await registerUser();

        if (response.statusCode == 200) {
          Navigator.pushNamed(context, "/login");
        } else {
          _showSnackBar(
            context,
            "Erreur lors de l'inscription : ${response.body}",
            Colors.red,
          );
        }
      },
      text: "S'inscrire",
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[300],
      body: SafeArea(
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Column(
              children: [
                const SizedBox(height: 50),
                _buildLogo(),
                const SizedBox(height: 50),
                Text(
                  "Bienvenue sur BOZ !",
                  style: TextStyle(color: Colors.grey[700]),
                ),
                const SizedBox(height: 25),
                _buildTextField(
                  controller: emailController,
                  hintText: "Email",
                  obscureText: false,
                ),
                const SizedBox(height: 25),
                _buildTextField(
                  controller: usernameController,
                  hintText: "Nom d'utilisateur",
                  obscureText: false,
                ),
                const SizedBox(height: 25),
                _buildTextField(
                  controller: passwordController,
                  hintText: "Mot de passe",
                  obscureText: true,
                ),
                const SizedBox(height: 25),
                _buildSignUpButton(context),
                const SizedBox(height: 25),
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
      ),
    );
  }
}
