<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documents Export</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        .meta { color: #666; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Documents Report</h1>
    <p class="meta">Generated on {{ now()->format('d M Y, H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Filename</th>
                <th>Type</th>
                <th>Status</th>
                <th>Student Name</th>
                <th>Student Number</th>
                <th>University</th>
                <th>Study Program</th>
                <th>GPA</th>
                <th>Confidence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documents as $doc)
                <tr>
                    <td>{{ $doc->id }}</td>
                    <td>{{ $doc->original_filename }}</td>
                    <td>{{ strtoupper($doc->file_type) }}</td>
                    <td>{{ $doc->status }}</td>
                    <td>{{ $doc->academicRecord?->student_name ?? '-' }}</td>
                    <td>{{ $doc->academicRecord?->student_number ?? '-' }}</td>
                    <td>{{ $doc->academicRecord?->university ?? '-' }}</td>
                    <td>{{ $doc->academicRecord?->study_program ?? '-' }}</td>
                    <td>{{ $doc->academicRecord?->gpa ?? '-' }}</td>
                    <td>{{ $doc->ocrResult?->confidence_score ?? '-' }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
