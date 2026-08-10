import 'package:flutter/material.dart';
import '../screens/auth/login_screen.dart';
import '../screens/auth/onboarding_screen.dart';
import '../screens/auth/register_screen.dart';
import '../screens/auth/splash_screen.dart';
import '../screens/home/home_screen.dart';
import '../screens/identity/identity_screen.dart';
import '../screens/identity/claims_screen.dart';
import '../screens/identity/create_claim_screen.dart';
import '../screens/proofs/proofs_screen.dart';
import '../screens/proofs/proof_detail_screen.dart';
import '../screens/proofs/qr_share_screen.dart';
import '../screens/history/history_screen.dart';
import '../screens/profile/profile_screen.dart';
import '../screens/profile/settings_screen.dart';
import '../screens/profile/about_screen.dart';

class AppRoutes {
  static const String splash = '/splash';
  static const String onboarding = '/onboarding';
  static const String login = '/login';
  static const String register = '/register';

  static const String home = '/home';
  static const String identity = '/identity';
  static const String claims = '/claims';
  static const String createClaim = '/claims/create';
  static const String proofs = '/proofs';
  static const String proofDetail = '/proofs/detail';
  static const String qrShare = '/proofs/share';
  static const String history = '/history';
  static const String profile = '/profile';
  static const String settings = '/settings';
  static const String about = '/about';
}

class AppRouter {
  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case AppRoutes.splash:
        return MaterialPageRoute(builder: (_) => const SplashScreen());
      case AppRoutes.onboarding:
        return MaterialPageRoute(builder: (_) => const OnboardingScreen());
      case AppRoutes.login:
        return MaterialPageRoute(builder: (_) => const LoginScreen());
      case AppRoutes.register:
        return MaterialPageRoute(builder: (_) => const RegisterScreen());
      case AppRoutes.home:
        return MaterialPageRoute(builder: (_) => const HomeScreen());
      case AppRoutes.identity:
        return MaterialPageRoute(builder: (_) => const IdentityScreen());
      case AppRoutes.claims:
        return MaterialPageRoute(builder: (_) => const ClaimsScreen());
      case AppRoutes.createClaim:
        return MaterialPageRoute(builder: (_) => const CreateClaimScreen());
      case AppRoutes.proofs:
        return MaterialPageRoute(builder: (_) => const ProofsScreen());
      case AppRoutes.proofDetail:
        final args = settings.arguments as Map<String, dynamic>? ?? {};
        return MaterialPageRoute(
          builder: (_) => ProofDetailScreen(
            proof: args['proof'] as Map<String, dynamic>? ?? const {},
          ),
        );
      case AppRoutes.qrShare:
        final args = settings.arguments as Map<String, dynamic>? ?? {};
        return MaterialPageRoute(
          builder: (_) => QrShareScreen(
            proofId: args['proofId'] as String? ?? '0',
            qrContent: args['qrContent'] as String? ?? '',
            expiresAt: args['expiresAt'] as String? ?? '',
          ),
        );
      case AppRoutes.history:
        return MaterialPageRoute(builder: (_) => const HistoryScreen());
      case AppRoutes.profile:
        return MaterialPageRoute(builder: (_) => const ProfileScreen());
      case AppRoutes.settings:
        return MaterialPageRoute(builder: (_) => const SettingsScreen());
      case AppRoutes.about:
        return MaterialPageRoute(builder: (_) => const AboutScreen());
      default:
        return MaterialPageRoute(builder: (_) => const SplashScreen());
    }
  }
}
