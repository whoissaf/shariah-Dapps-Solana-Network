import 'package:flutter/material.dart';

void main() {
  runApp(const IdentityWalletApp());
}

class IdentityWalletApp extends StatelessWidget {
  const IdentityWalletApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Identity Wallet',
      theme: ThemeData(
        colorSchemeSeed: const Color(0xFF10B981),
        useMaterial3: true,
      ),
      home: const SplashScreen(),
    );
  }
}

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(
        child: Text('Identity Wallet'),
      ),
    );
  }
}
