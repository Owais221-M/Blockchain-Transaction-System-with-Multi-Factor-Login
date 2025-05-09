# Blockchain Transaction System with Multi-Factor Login

A secure, full-stack crypto trading platform that demonstrates how blockchain technology and multi-factor authentication (MFA) can be combined to ensure transactional integrity and user protection. This project simulates ETH trading on the Ethereum blockchain using signed raw transactions while enforcing OTP-based login to guard against unauthorized access.

---

## 🚀 Overview

This system allows users to:

* Register with a unique Ethereum address and private key
* Log in using multi-factor authentication (OTP)
* Buy and sell ETH with USDT (virtual)
* Execute real ETH transfers on a local blockchain (Ganache)
* Sign transactions with private keys (no unlocked accounts)
* View and sync ETH balances directly from the blockchain

All actions are logged, validated, and securely managed via smart transaction flows and server-side protection.

---

## 🔐 Key Features

| Feature                             | Description                                                                       |
| ----------------------------------- | --------------------------------------------------------------------------------- |
| 🔗 **On-Chain ETH Transactions**    | Real ETH transfers using `eth_sendRawTransaction`, signed with user's private key |
| 🔑 **Multi-Factor Authentication**  | Secure OTP-based login via email using PHPMailer                                  |
| 🔐 **Private Key Isolation**        | Each user has their own Ethereum keypair; no keys are exposed or reused           |
| 🔄 **Live Blockchain Balance Sync** | ETH balance fetched from Ganache using `eth_getBalance` and stored in SQL         |
| 🧠 **Smart Validation**             | All balances, OTPs, and blockchain state transitions are validated in PHP         |

---

## 🛠️ Tech Stack

* **Backend**: PHP (with Composer), MySQL
* **Blockchain**: Ganache CLI (Ethereum test network)
* **Libraries**:

  * [`web3p/web3.php`](https://github.com/web3p/web3.php)
  * [`web3p/ethereum-tx`](https://github.com/web3p/ethereum-tx)
  * [`PHPMailer`](https://github.com/PHPMailer/PHPMailer)
* **Frontend**: HTML, CSS
* **Server**: XAMPP / Apache (localhost)

---

## 🧰 Folder Structure

```
project-root/
├── backend/
│   ├── buy_sell.php
│   ├── get_balance.php
│   ├── send_otp.php
│   ├── verify_otp.php
│   └── vendor/ (Composer libraries)
├── frontend/
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   └── index.css
├── database/
│   └── crypto_transaction.sql
├── .gitignore
├── README.md
```

---

## ⚙️ Setup Instructions

1. **Start Ganache CLI** (with deterministic accounts):

   ```bash
   ganache-cli -d
   ```

   ➤ Download: [Ganache CLI](https://trufflesuite.com/ganache/)

2. **Install PHP Dependencies via Composer**

   ```bash
   composer install
   ```

   ➤ Download: [Composer](https://getcomposer.org/)

3. **Import SQL Schema**
   Use `phpMyAdmin` or CLI to import `database/crypto_transaction.sql` into MySQL.

4. **Configure Email OTP**
   Set up email credentials in `send_otp.php` using PHPMailer.
   ➤ Download: [PHPMailer GitHub](https://github.com/PHPMailer/PHPMailer)

5. **Run on Localhost**
   Serve the project using XAMPP or any local Apache server:

   ```
   http://localhost/crypto_transaction/
   ```

6. **Register → OTP Login → Trade**

---

## 📄 Security Architecture

* ✅ **Blockchain signing**: Private key used to sign ETH transactions with `ethereum-tx`
* ✅ **No unlocked accounts**: Ganache is accessed via raw RPC only
* ✅ **MFA enforced**: OTP sent via email is required to access user dashboard
* ✅ **SQL Sanitization**: All queries prepared with bound parameters
* ✅ **Balance integrity**: All ETH balances are fetched from Ganache, not faked

---

## 🧑‍💼 Ideal For

* Blockchain Developer portfolios
* Backend Engineer job applications
* Demonstrating system security knowledge (MFA, wallet isolation, key signing)
* Students applying for crypto/web3 internships or junior dev roles

---

## 📜 License

This project is open-source under the MIT License.
Feel free to fork, customize, and contribute.

---

## 🌐 Related Project

You can also view the initial version of this platform (focused on basic web programming) here:

🔗 [Crypto Trading Simulator – Web Programming Version](https://github.com/Owais221-M/crypto-transaction-web)

