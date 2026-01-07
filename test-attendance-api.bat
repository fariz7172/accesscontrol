@echo off
echo ========================================
echo Testing Attendance Report API (POST with JSON)
echo ========================================
echo.

echo [INFO] Make sure you have a valid JWT token first!
echo Run test-jwt-api.bat to get a token if you haven't.
echo.
set /p TOKEN="Enter your JWT token: "
echo.

echo ========================================
echo [1/5] Testing Get User List (GET)
echo ========================================
curl -X GET "http://localhost:8000/api/attendance/users" ^
  -H "Authorization: Bearer %TOKEN%"
echo.
echo.

echo ========================================
echo [2/5] Testing Get User Attendance (Single User - POST JSON)
echo ========================================
curl -X POST "http://localhost:8000/api/attendance/report" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{\"user_id\":[1],\"start_date\":\"2026-01-01\",\"end_date\":\"2026-01-31\"}"
echo.
echo.

echo ========================================
echo [3/5] Testing Multiple Users (POST JSON)
echo ========================================
curl -X POST "http://localhost:8000/api/attendance/report" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{\"user_id\":[1,2,3],\"start_date\":\"2026-01-01\",\"end_date\":\"2026-01-31\"}"
echo.
echo.

echo ========================================
echo [4/5] Testing With Details (POST JSON)
echo ========================================
curl -X POST "http://localhost:8000/api/attendance/report" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{\"user_id\":[1],\"start_date\":\"2026-01-01\",\"end_date\":\"2026-01-31\",\"include_details\":true}"
echo.
echo.

echo ========================================
echo [5/5] Testing Get Statistics (POST JSON)
echo ========================================
curl -X POST "http://localhost:8000/api/attendance/statistics" ^
  -H "Authorization: Bearer %TOKEN%" ^
  -H "Content-Type: application/json" ^
  -d "{\"start_date\":\"2026-01-01\",\"end_date\":\"2026-01-31\"}"
echo.
echo.

echo ========================================
echo Test Complete!
echo ========================================
echo.
pause
