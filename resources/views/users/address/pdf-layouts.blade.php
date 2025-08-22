<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Address Verification Report</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      margin: 20px;
      color: #222;
    }
    h2 {
      text-align: center;
      margin-bottom: 10px;
      font-size: 18px;
      text-transform: uppercase;
    }
    .section {
      margin-bottom: 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      padding: 8px;
    }
    .section-title {
      font-weight: bold;
      margin-bottom: 5px;
      border-bottom: 1px solid #eee;
      padding-bottom: 3px;
      font-size: 14px;
      color: #444;
    }
    .row {
      display: flex;
      flex-wrap: wrap;
      margin-bottom: 4px;
    }
    .col {
      flex: 1 0 45%;
      margin-right: 10px;
      margin-bottom: 4px;
    }
    .label {
      font-weight: bold;
      display: inline-block;
      width: 140px;
    }
    .value {
      display: inline-block;
    }
    .status {
      padding: 4px 8px;
      background: #e6f5ea;
      border: 1px solid #b5e2c4;
      color: #257a3e;
      display: inline-block;
      font-size: 12px;
      border-radius: 3px;
      margin-top: 5px;
    }
    .notes {
      font-style: italic;
      margin-top: 5px;
    }
  </style>
</head>
<body>
{!! $content !!}
</body>
</html>
