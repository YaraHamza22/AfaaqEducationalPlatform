<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Certificate</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .wrapper {
            width: 100%;
            height: 100%;
            padding: 40px;
            box-sizing: border-box;
            border: 8px solid #0f172a;
            text-align: center;
        }
        .title {
            font-size: 44px;
            margin-top: 30px;
            margin-bottom: 20px;
            color: #0f172a;
        }
        .subtitle {
            font-size: 20px;
            margin-bottom: 24px;
        }
        .student {
            font-size: 34px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .course {
            font-size: 28px;
            margin-bottom: 20px;
        }
        .meta {
            font-size: 16px;
            margin-top: 26px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="title">Certificate of Completion</div>
        <div class="subtitle">This certifies that</div>
        <div class="student">{{ $certificate->student?->name ?? 'Student' }}</div>
        <div class="subtitle">has successfully completed the course</div>
        <div class="course">{{ data_get($certificate->course?->title, 'en', $certificate->course?->slug ?? 'Course') }}</div>
        <div class="meta">
            Weighted Score: {{ number_format((float) $certificate->weighted_percentage, 2) }}%<br>
            Issued At: {{ optional($certificate->issued_at)->format('Y-m-d H:i') }}<br>
            Certificate ID: #{{ $certificate->id }}
        </div>
    </div>
</body>
</html>
