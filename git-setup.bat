@echo off
echo 🏥 Hospital Management System - Git Setup
echo ==========================================

echo 📋 Step 1: Initializing Git repository...
git init

echo 📋 Step 2: Adding all files to Git...
git add .

echo 📋 Step 3: Creating initial commit...
git commit -m "Initial commit: Complete Hospital Management System v1.0"

echo 📋 Step 4: Setting up main branch...
git branch -M main

echo 📋 Step 5: Adding GitHub remote...
git remote add origin https://github.com/myouseef/Dental_app.git

echo 📋 Step 6: Pushing to GitHub...
git push -u origin main

echo 🎉 Git setup completed successfully!
echo ==========================================
echo 📝 Your project is now available at:
echo https://github.com/myouseef/Dental_app
echo ==========================================
pause