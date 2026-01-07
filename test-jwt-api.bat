@echo off
echo ========================================
echo Testing JWT Authentication API
echo ========================================
echo.

echo [1/4] Testing Login Endpoint...
echo.
curl -X POST http://localhost:8000/api/auth/login -H "Content-Type: application/json" -d "{\"username\":\"admin\",\"password\":\"admin\"}"
echo.
echo.

echo [2/4] Testing Login with Invalid Credentials...
echo.
curl -X POST http://localhost:8000/api/auth/login -H "Content-Type: application/json" -d "{\"username\":\"admin\",\"password\":\"wrong\"}"
echo.
echo.

echo [3/4] Testing Validation Error...
echo.
curl -X POST http://localhost:8000/api/auth/login -H "Content-Type: application/json" -d "{}"
echo.
echo.

echo ========================================
echo Test Complete!
echo ========================================
echo.
echo Note: Copy the access_token from successful login
echo Then test protected endpoints:
echo.
echo curl -X GET http://localhost:8000/api/auth/me -H "Authorization: Bearer YOUR_TOKEN"
echo curl -X POST http://localhost:8000/api/auth/refresh -H "Authorization: Bearer YOUR_TOKEN"
echo curl -X POST http://localhost:8000/api/auth/logout -H "Authorization: Bearer YOUR_TOKEN"
echo.
pause
