# IAI Zlecanie Listow - Installation Guide

## Current Issue Analysis

Based on your setup output, here are the problems:

### 1. ❌ Wrong File Structure
Your setup.php is running from: `C:\xampp\htdocs\IAI_zlecanie_listow\public`
But it should be at: `C:\xampp\htdocs\IAI_zlecanie_listow\` (root level)

### 2. ❌ Missing ODBC Driver 
The error shows: "This extension requires the Microsoft ODBC Driver for SQL Server"

### 3. ✅ PHP Extensions are OK
- sqlsrv: ✓ Loaded
- pdo_sqlsrv: ✓ Loaded

## STEP-BY-STEP FIX

### Step 1: Fix File Structure
1. Move all files from `C:\xampp\htdocs\IAI_zlecanie_listow\public\` to `C:\xampp\htdocs\IAI_zlecanie_listow\`
2. Keep only the actual PUBLIC files in the public folder
3. The structure should look like:

```
C:\xampp\htdocs\IAI_zlecanie_listow\
├── setup.php                    ← (move from public/)
├── debug_search.php             ← (move from public/)
├── glowny.php                   ← (move from public/)
├── config/
│   └── config.php
├── public/
│   ├── search_orders.php
│   ├── generate_labels.php
│   ├── print_labels.php
│   └── (other .php files)
├── views/
│   └── order_view.php
├── storage/
└── logs/
```

### Step 2: Install Microsoft ODBC Driver
1. Download from: https://go.microsoft.com/fwlink/?LinkId=163712
2. Install the Microsoft ODBC Driver 17 for SQL Server (x64)
3. Restart Apache after installation

### Step 3: Test Again
1. Access: `http://localhost/IAI_zlecanie_listow/setup.php` (from root, not public/)
2. All checks should now pass

### Step 4: Access Application
- Main app: `http://localhost/IAI_zlecanie_listow/views/order_view.php`
- Search test: `http://localhost/IAI_zlecanie_listow/debug_search.php`

## Why This Happened
The confusion occurred because:
1. Your main computer: Files are in `g:\Mój dysk\phphtdoc\IAI_zlecanie_listow_d\`
2. Other computer: Files were copied to `C:\xampp\htdocs\IAI_zlecanie_listow\public\`

The setup script assumed it was running from the project root, but it was actually inside the public folder, which caused it to create the wrong directory structure.