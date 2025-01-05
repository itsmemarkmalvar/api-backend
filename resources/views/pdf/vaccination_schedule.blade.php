<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vaccination Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #2E3A59;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 5px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2E3A59;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #eee;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .vaccine-item {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .vaccine-name {
            font-weight: bold;
            color: #2E3A59;
            margin-bottom: 5px;
        }
        .vaccine-details {
            font-size: 14px;
            color: #666;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Vaccination Schedule</div>
        <div class="subtitle">Generated on {{ $generatedDate }}</div>
    </div>

    <div class="section">
        <div class="section-title">Child Information</div>
        <div class="info-row">
            <span class="info-label">Name:</span>
            {{ $baby->name }}
        </div>
        <div class="info-row">
            <span class="info-label">Date of Birth:</span>
            {{ Carbon\Carbon::parse($baby->birthdate)->format('F d, Y') }}
        </div>
        <div class="info-row">
            <span class="info-label">Gender:</span>
            {{ ucfirst($baby->gender) }}
        </div>
    </div>

    <div class="section">
        <div class="section-title">Completed Vaccinations</div>
        @if($completed->count() > 0)
            @foreach($completed as $vaccine)
                <div class="vaccine-item">
                    <div class="vaccine-name">{{ $vaccine->vaccine_name }}</div>
                    <div class="vaccine-details">
                        Completed on: {{ Carbon\Carbon::parse($vaccine->date)->format('F d, Y') }}
                        @if($vaccine->administered_by)
                            <br>Administered by: {{ $vaccine->administered_by }}
                        @endif
                        @if($vaccine->notes)
                            <br>Notes: {{ $vaccine->notes }}
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <p>No completed vaccinations.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Scheduled Vaccinations</div>
        @if($scheduled->count() > 0)
            @foreach($scheduled as $vaccine)
                <div class="vaccine-item">
                    <div class="vaccine-name">{{ $vaccine->vaccine_name }}</div>
                    <div class="vaccine-details">
                        Scheduled for: {{ Carbon\Carbon::parse($vaccine->date)->format('F d, Y') }}
                        @if($vaccine->notes)
                            <br>Notes: {{ $vaccine->notes }}
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <p>No scheduled vaccinations.</p>
        @endif
    </div>

    <div class="footer">
        This document was generated automatically. Please consult with your healthcare provider for any questions.
    </div>
</body>
</html> 