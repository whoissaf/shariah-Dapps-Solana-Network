import 'package:flutter/material.dart';
import 'design/theme.dart';
import 'navigation/app_router.dart';

void main() {
  runApp(const IdentityWalletApp());
}

class IdentityWalletApp extends StatelessWidget {
  const IdentityWalletApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Identity Wallet',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.build(),
      initialRoute: AppRoutes.splash,
      onGenerateRoute: AppRouter.generateRoute,
    );
  }
}
