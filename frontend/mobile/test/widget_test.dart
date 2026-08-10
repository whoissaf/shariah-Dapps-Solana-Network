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
  });
}
