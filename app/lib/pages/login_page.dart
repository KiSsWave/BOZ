import 'package:boz/components/my_button.dart';
import 'package:boz/components/my_textfield.dart';
import 'package:flutter/material.dart';
import 'package:boz/services/remote_service.dart';
import 'package:http/http.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({Key? key}) : super(key: key);

  @override
  _LoginPageState createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final emailController = TextEditingController();
  final passwordController = TextEditingController();
  bool _isPasswordVisible = false;

  Future<Response> signUserIn() async {
    final email = emailController.text;
    final password = passwordController.text;

    return await RemoteService().loginUser(email, password);
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

  Widget _buildSignInButton(BuildContext context) {
    return MyButton(
      onTap: () async {
        _showSnackBar(context, "Connexion en cours ...", Colors.black);

        final response = await signUserIn();

        if (response.statusCode == 200) {
          int? role = await RemoteService().getRole();
          switch (role) {
            case 1:
              Navigator.pushReplacementNamed(context, "/home");
              break;
            case 2:
              Navigator.pushReplacementNamed(context, "/seller");
              break;
            default:
              _showSnackBar(
                context,
                "Erreur lors de la connexion: rôle inconnu",
                Colors.red,
              );
          }
        } else {
          _showSnackBar(
            context,
            "Erreur lors de la connexion: ${response.body}",
            Colors.red,
          );
        }
      },
      text: "Se connecter",
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[300],
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            children: [
              const SizedBox(height: 50),
              _buildLogo(),
              const SizedBox(height: 50),
              Text(
                "Content de vous revoir !",
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
                controller: passwordController,
                hintText: "Mot de passe",
                obscureText: true,
                isPasswordField: true,
              ),
              const SizedBox(height: 25),
              _buildSignInButton(context),
              const SizedBox(height: 25),
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Text("Nouveau sur l'application ?"),
                  TextButton(
                    onPressed: () {
                      Navigator.pushNamed(context, "/register");
                    },
                    child: const Text("Créer un compte"),
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
