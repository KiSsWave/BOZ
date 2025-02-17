import 'package:flutter/material.dart';

class FAQPage extends StatelessWidget {
  final List<Map<String, String>> _faqItems = [
    {
      "question": "Je souhaite devenir vendeur, comment je fais ?",
      "answer": "Faites un ticket en indiquant que vous voulez devenir vendeur, et on s'en occupe !",
    },
    {
      "question": "Je souhaite créditer mon compte, comment cela se passe ?",
      "answer": "Faites un ticket en indiquant le montant que vous voulez créditer et un administrateur rechargera votre compte",
    },
    {
      "question": "Qui êtes vous ?",
      "answer": "Nous sommes une équipe de 4 développeurs passionnés",
    },
    {
      "question": "J'ai payé une facture et il me reste de l'argent, puis-je le récupérer sur mon compte ?",
      "answer": "Oui, dans l'onglet paramètres une option est disponible pour transférer l'argent sur un compte bancaire.",
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey.shade300,
      appBar: AppBar(title: const Text("FAQ")),
      body: ListView.builder(
        padding: const EdgeInsets.all(16.0),
        itemCount: _faqItems.length,
        itemBuilder: (context, index) {
          return Card(
            color: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12.0)),
            elevation: 2,
            child: ExpansionTile(
              title: Text(_faqItems[index]['question']!, style: const TextStyle(fontSize: 18.0, fontWeight: FontWeight.bold)),
              children: [
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Text(_faqItems[index]['answer']!, style: const TextStyle(fontSize: 16.0)),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
