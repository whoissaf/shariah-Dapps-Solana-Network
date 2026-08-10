# 🔐 Identity Wallet — Sharia-Compliant Zero-Knowledge Proof Verification

Private identity wallet for Sharia-compliant finance using Zero-Knowledge Proofs on Ethereum Sepolia testnet. Prove eligibility (income, age, halal business) without revealing sensitive personal data.

## 🏆 Hackathon Project

Built for **Ethereum Privacy Hackathon 2026** — demonstrating how Semaphore + Groth16 ZK proofs enable privacy-preserving compliance for Islamic finance.

## 🛠️ Tech Stack

**Backend**
- Laravel 11 (PHP 8.4) — REST API
- PostgreSQL 17 — Primary database
- Laravel Sanctum — Token authentication

**Mobile (Flutter)**
- Flutter 3.22+
- Material 3 Design System
- Emerald Green + Deep Navy color palette

**Web (Next.js)**
- Next.js 15 (App Router)
- TypeScript
- Static Export (output: export)

**CI/CD**
- GitHub Actions — Automated Flutter APK build + Next.js static build
- Auto-upload artifacts on every push to main

## ✨ Key Features

**Privacy-First Architecture**
- Anonymous Identity (Semaphore identity commitment)
- Zero-Knowledge Proofs (Groth16 circuit)
- On-chain hash anchoring (Ethereum Sepolia)
- QR-based proof sharing with signature verification

**Sharia Compliance Engine**
- 4 claim types: Income Threshold, Age Minimum, Halal Business, No Restricted Financing
- Rule Engine with configurable parameters
- AI-powered verification explanation (LLM simulation)

**Dual Interface**
- 📱 **Flutter Mobile** (15 screens) — User identity wallet
- 💻 **Next.js Web** (12 screens) — Verifier compliance portal

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.4+
- Composer
- PostgreSQL 17+
- Node.js 22+
- Flutter SDK 3.22+

### 1. Backend Setup (Laravel)

    cd backend
    composer install
    cp .env.example .env
    php artisan key:generate

Configure PostgreSQL in .env:

    DB_CONNECTION=pgsql
    DB_DATABASE=hackathon
    DB_USERNAME=hackathon
    DB_PASSWORD=hackathon

Run migrations and seed:

    php artisan migrate:fresh --seed
    php artisan serve

Backend runs at http://localhost:8000

**Demo accounts (from seeder):**
- User: user@example.com / password123
- Verifier: verifier@example.com / password123

### 2. Mobile Setup (Flutter)

    cd frontend/mobile
    flutter pub get
    flutter run

### 3. Web Setup (Next.js)

    cd frontend/web
    npm install
    npm run dev

Web runs at http://localhost:3000

---

## 🤖 GitHub Actions (Automated Build)

Every push to main triggers:

**Flutter Build** (.github/workflows/flutter-build.yml)
- Runs flutter analyze + flutter test
- Builds release APK
- Uploads artifact: identity-wallet-apk

**Next.js Build** (.github/workflows/nextjs-build.yml)
- Runs npm run build (static export)
- Uploads artifact: verifier-web-static

👉 **Download APK and static site from Actions tab**

---

## 📁 Repository Structure

    hackathon/
    ├── backend/                    # Laravel API
    │   ├── app/
    │   │   ├── Http/Controllers/
    │   │   ├── Models/
    │   │   └── Services/
    │   ├── database/migrations/
    │   ├── routes/api.php
    │   └── tests/Feature/          # 171 tests, 741 assertions
    │
    ├── frontend/
    │   ├── mobile/                 # Flutter Identity Wallet
    │   │   ├── lib/
    │   │   │   ├── design/
    │   │   │   ├── navigation/
    │   │   │   └── screens/        # 15 screens
    │   │   └── test/
    │   │
    │   └── web/                    # Next.js Verifier Portal
    │       ├── app/
    │       │   ├── components/
    │       │   ├── dashboard/      # 12 screens
    │       │   └── login/
    │       └── globals.css
    │
    ├── docs/
    │   ├── backend-endpoints.json
    │   └── frontend/
    │
    └── .github/workflows/
        ├── flutter-build.yml
        └── nextjs-build.yml

---

## 🔌 API Endpoints

### Flutter (User) — 10 workflows

| Endpoint | Description |
|----------|-------------|
| POST /auth/register | Create account |
| POST /auth/verify-email | Email OTP verification |
| POST /auth/login | Login with token |
| POST /wallet/connect | Connect Ethereum wallet |
| POST /identity/create | Generate anonymous identity |
| POST /claims/create | Create eligibility claim |
| POST /documents/upload | Upload supporting docs |
| POST /rules/validate | Rule engine validation |
| POST /proof/generate | Generate ZK proof |
| POST /proof/share | Generate QR with signature |

### Web (Verifier) — 10 workflows

| Endpoint | Description |
|----------|-------------|
| GET /dashboard | Verification queue stats |
| POST /verification/read | Read QR proof |
| POST /verification/verify | Verify ZK proof |
| POST /ai/explain | AI recommendation |
| POST /verification/approve | Approve verification |
| POST /verification/reject | Reject with reason |
| GET /audit | Complete audit trail |
| GET /report/export | Export CSV report |

Full API contract: docs/backend-endpoints.json

---

## 🎬 Demo Script (3-4 minutes)

### Scene 1: User Journey (Flutter)

1. Open Identity Wallet app
2. Register → Email OTP → Login
3. Connect wallet (0x742d...f0bEb1)
4. Create anonymous identity
5. Create Income Threshold claim (IDR 7M)
6. Upload salary slip
7. Rule validation → Eligible
8. Generate ZK proof
9. Share QR code

### Scene 2: Verifier Journey (Next.js)

1. Open Verifier Portal
2. Login as compliance officer
3. Dashboard shows pending queue
4. Scan QR (or manual input)
5. View proof detail + verification checks
6. AI explains recommendation (Approve 94%)
7. Approve verification
8. View audit trail with blockchain TX
9. Export CSV report

### Scene 3: On-Chain Proof

1. Show Ethereum TX on Etherscan Sepolia
2. Proof hash anchored: 0x943a12fb...8c21d4
3. Block number: 19,847,231
4. Complete audit trail from user → verifier → blockchain

---

## 🧪 Testing

**Backend**: 171 tests, 741 assertions (100% pass rate)

    cd backend
    php artisan test

**Flutter**: 11 widget tests

    cd frontend/mobile
    flutter test

---

## 📄 License

MIT License — Open source for hackathon demonstration.

---

## 🙏 Acknowledgments

- **Semaphore Protocol** — Zero-knowledge identity
- **Ethereum Foundation** — Privacy-focused blockchain
- **Laravel, Flutter, Next.js** — Robust frameworks

---

**Built with ❤️ for Ethereum Privacy Hackathon 2026**

*Private identity. Sharia compliance. Zero-knowledge proofs.*
