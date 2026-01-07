@echo off
echo ========================================
echo Testing Open Gate API
echo ========================================
echo.

echo [INFO] Make sure you have a valid JWT token first!
echo Run test-jwt-api.bat to get a token if you haven't.
echo.
set /p TOKEN="Enter your JWT token: "
echo.

echo ========================================
echo [1/5] Testing Open Gate by Device ID (Soyal - Type 1)
echo ========================================
curl -X POST "http://localhost:8000/api/gate/open" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{\"device_id\":4}"
echo.
echo.

echo ========================================
echo [2/5] Testing Open Gate by Serial Number (Tasoft - Type 0/50)
echo ========================================
curl -X POST "http://localhost:8000/api/gate/open" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{\"device_sn\":\"ZYSL20032920\"}"
echo.
echo.

echo ========================================
echo [3/5] Testing Device Not Found
echo ========================================
curl -X POST "http://localhost:8000/api/gate/open" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{\"device_id\":999}"
echo.
echo.

echo ========================================
echo [4/5] Testing Validation Error (No Parameters)
echo ========================================
curl -X POST "http://localhost:8000/api/gate/open" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{}"
echo.
echo.

echo ========================================
echo [5/5] Testing Unauthorized (No Token)
echo ========================================
curl -X POST "http://localhost:8000/api/gate/open" ^
  -H "Content-Type: application/json" ^
  -d "{\"device_id\":1}"
echo.
echo.

echo ========================================
echo Test Complete!
echo ========================================
echo.
pause
