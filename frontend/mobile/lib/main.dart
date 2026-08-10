import 'package:flutter/material.dart';
import 'design/theme.dart';
import 'design/components/app_card.dart';
import 'design/components/empty_state.dart';
import 'design/components/mono_hash.dart';
import 'design/components/status_badge.dart';

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
      home: const SplashScreen(),
    );
  }
}

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Spacer(),
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.shield_outlined,
                  color: Colors.white,
                  size: 32,
                ),
              ),
              const SizedBox(height: 24),
              Text(
                'Identity Wallet',
                style: Theme.of(context).textTheme.headlineLarge,
              ),
              const SizedBox(height: 8),
              Text(
                'Private identity for compliant finance.',
                style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                      color: AppColors.textSecondary,
                    ),
              ),
              const Spacer(),
              const AppCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Preview',
                          style: TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        StatusBadge(label: 'Active', kind: StatusKind.success),
                      ],
                    ),
                    SizedBox(height: 12),
                    MonoHash(value: '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb1'),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              const EmptyState(
                icon: Icons.fingerprint,
                title: 'Design System Ready',
                subtitle: 'Foundation components loaded.',
              ),
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: () {},
                child: const Text('Continue'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
