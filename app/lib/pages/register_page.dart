import 'package:boz/components/my_button.dart';
import 'package:boz/components/my_textfield.dart';
import 'package:boz/services/remote_service.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({Key? key}) : super(key: key);

  @override
  _RegisterPageState createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  final usernameController = TextEditingController();
  final passwordController = TextEditingController();
  final emailController = TextEditingController();
  bool _isPasswordVisible = false;

  Future<Response> registerUser() async {
    final email = emailController.text;
    final username = usernameController.text;
    final password = passwordController.text;

    return await RemoteService().registerUser(email, username, password);
  }

  bool _validatePassword(String password) {
    // Password validation:
    // - Minimum 8 characters
    // - At least one uppercase letter
    // - At least one number
    // - At least one special character
    final passwordRegex = RegExp(
        r'^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{8,}$');
    return passwordRegex.hasMatch(password);
  }

  void _showSnackBar(BuildContext context, String message, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, style: const TextStyle(color: Colors.white)),
        backgroundColor: color,
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hintText,
    required bool obscureText,
    bool? isPasswordField,
  }) {
    return MyTextField(
      controller: controller,
      hintText: hintText,
      obscureText: isPasswordField == true ? !_isPasswordVisible : obscureText,
      suffixIcon: isPasswordField == true
          ? IconButton(
              icon: Icon(
                _isPasswordVisible ? Icons.visibility : Icons.visibility_off,
              ),
              onPressed: () {
                setState(() {
                  _isPasswordVisible = !_isPasswordVisible;
                });
              },
            )
          : null,
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
        final password = passwordController.text;

        if (!_validatePassword(password)) {
          _showSnackBar(
            context,
            "Le mot de passe doit contenir au moins 8 caractères, "
            "une majuscule, un chiffre et un caractère spécial",
            Colors.red,
          );
          return;
        }

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
                  isPasswordField: true,
                ),
                const SizedBox(height: 10),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Text(
                    "Le mot de passe doit contenir au moins 8 caractères, "
                    "une majuscule, un chiffre et un caractère spécial",
                    style: TextStyle(
                      color: Colors.grey[600],
                      fontSize: 12,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ),
                const SizedBox(height: 15),
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
