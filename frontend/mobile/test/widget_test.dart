import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:identity_wallet/main.dart';
import 'package:identity_wallet/navigation/app_router.dart';

void main() {
  testWidgets('splash renders brand', (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pump();

    expect(find.text('Identity Wallet'), findsOneWidget);
    expect(find.byIcon(Icons.shield_outlined), findsOneWidget);
  });

  testWidgets('home dashboard renders all status cards',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.home);
    await tester.pumpAndSettle();

    expect(find.text('Welcome back'), findsOneWidget);
    expect(find.text('Privacy Score'), findsOneWidget);
    expect(find.text('85/100'), findsOneWidget);
    expect(find.text('Wallet'), findsOneWidget);
    expect(find.text('Identity'), findsOneWidget);
    expect(find.text('Latest Proof'), findsOneWidget);
    expect(find.text('Verification'), findsOneWidget);
    expect(find.byIcon(Icons.home), findsOneWidget);
  });

  testWidgets('claims screen renders all 4 claim types',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.claims);
    await tester.pumpAndSettle();

    expect(find.text('My Claims'), findsOneWidget);
    expect(find.text('Income Threshold'), findsOneWidget);
    expect(find.text('Age Minimum'), findsOneWidget);
    expect(find.text('Business Category'), findsOneWidget);
    expect(find.text('No Restricted Financing'), findsOneWidget);
    expect(find.text('New Claim'), findsOneWidget);
  });

  testWidgets('identity screen renders hero card',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.identity);
    await tester.pumpAndSettle();

    expect(find.text('My Identity'), findsOneWidget);
    expect(find.text('Anonymous Identity'), findsOneWidget);
    expect(find.text('Anonymous ID'), findsOneWidget);
    expect(find.text('Identity Commitment'), findsOneWidget);
    expect(find.text('ACTIVE'), findsOneWidget);
  });

  testWidgets('profile screen renders user info',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.profile);
    await tester.pumpAndSettle();

    expect(find.text('Profile'), findsOneWidget);
    expect(find.text('User Demo'), findsOneWidget);
    expect(find.text('user@example.com'), findsWidgets);
    expect(find.text('Logout'), findsOneWidget);
    expect(find.text('Settings'), findsOneWidget);
    expect(find.text('About'), findsOneWidget);
  });

  testWidgets('proofs list renders all 4 proofs',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.proofs);
    await tester.pumpAndSettle();

    expect(find.text('My Proofs'), findsOneWidget);
    expect(find.text('Zero-Knowledge Proofs'), findsOneWidget);
    expect(find.text('Income Threshold'), findsOneWidget);
    expect(find.text('Age Minimum'), findsOneWidget);
    expect(find.text('Business Category'), findsOneWidget);
    expect(find.text('No Restricted Financing'), findsOneWidget);
  });

  testWidgets('proof detail renders all info sections',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(
      AppRoutes.proofDetail,
      arguments: {
        'proof': {
          'id': '1',
          'claimType': 'Income Threshold',
          'status': 'Verified',
          'proofHash': '0x742d35cc6634c0532925a3b844bc9e7595f0beb1',
          'createdAt': '2 min ago',
          'blockchainTx': '0x943a12fb...8c21d4',
        },
      },
    );
    await tester.pumpAndSettle();

    expect(find.text('Proof Detail'), findsOneWidget);
    expect(find.text('Income Threshold'), findsOneWidget);
    expect(find.text('VERIFIED'), findsOneWidget);
    expect(find.text('Proof Information'), findsOneWidget);
    expect(find.text('Verification'), findsOneWidget);
    expect(find.text('Generate QR for Verifier'), findsOneWidget);
  });

  testWidgets('qr share screen renders full screen qr',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(
      AppRoutes.qrShare,
      arguments: {
        'proofId': '1',
        'qrContent': '{"proof_id":1,"nonce":"abc123","signature":"xyz789"}',
        'expiresAt': '10:00',
      },
    );
    await tester.pumpAndSettle();

    expect(find.text('Share Proof'), findsOneWidget);
    expect(find.text('Scan to Verify'), findsOneWidget);
    expect(find.text('#1'), findsOneWidget);
    expect(find.text('Expires in 10:00'), findsOneWidget);
    expect(find.text('Regenerate QR'), findsOneWidget);
    expect(find.text('Done'), findsOneWidget);
  });

  testWidgets('history screen renders timeline events',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.history);
    await tester.pumpAndSettle();

    expect(find.text('Verification History'), findsOneWidget);
    expect(find.text('Activity Timeline'), findsOneWidget);
    expect(find.text('Proof Verified'), findsOneWidget);
    expect(find.text('QR Scanned'), findsOneWidget);
    expect(find.text('Proof Generated'), findsOneWidget);
  });

  testWidgets('settings screen renders all sections',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.settings);
    await tester.pumpAndSettle();

    expect(find.text('Settings'), findsOneWidget);
    expect(find.text('Appearance'), findsOneWidget);
    expect(find.text('Notifications'), findsOneWidget);
    expect(find.text('Security'), findsOneWidget);
    expect(find.text('Dark Mode'), findsOneWidget);
    expect(find.text('Biometric Authentication'), findsOneWidget);
  });

  testWidgets('about screen renders app info',
      (WidgetTester tester) async {
    await tester.pumpWidget(const IdentityWalletApp());
    await tester.pumpAndSettle();

    tester.state<NavigatorState>(find.byType(Navigator)).pushNamed(AppRoutes.about);
    await tester.pumpAndSettle();

    expect(find.text('About'), findsOneWidget);
    expect(find.text('Identity Wallet'), findsOneWidget);
    expect(find.text('Application'), findsOneWidget);
    expect(find.text('Blockchain Network'), findsOneWidget);
    expect(find.text('Ethereum (Sepolia Testnet)'), findsOneWidget);
    expect(find.text('Semaphore + Groth16'), findsOneWidget);
    expect(find.text('MIT License'), findsOneWidget);
  });
}
