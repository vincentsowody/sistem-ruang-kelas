<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1F2937; background: #fff; }

    /* Header institusi */
    .header { border-bottom: 3px solid #1E3A5F; padding-bottom: 10px; margin-bottom: 16px; }
    .header-inner { display: table; width: 100%; }
    .header-logo { display: table-cell; width: 60px; vertical-align: middle; }
    .header-logo-box { width: 50px; height: 50px; background: #1E3A5F; border-radius: 6px;
                       text-align: center; line-height: 50px; font-size: 22px; color: #fff; font-weight: bold; }
    .header-text { display: table-cell; vertical-align: middle; padding-left: 12px; }
    .header-text h1 { font-size: 13px; font-weight: bold; color: #1E3A5F; }
    .header-text p  { font-size: 9px; color: #6B7280; margin-top: 2px; }
    .header-meta { display: table-cell; vertical-align: middle; text-align: right; font-size: 9px; color: #9CA3AF; }

    /* Judul laporan */
    .report-title { text-align: center; margin-bottom: 14px; }
    .report-title h2 { font-size: 14px; font-weight: bold; color: #1E3A5F; text-transform: uppercase; letter-spacing: 1px; }
    .report-title .subtitle { font-size: 10px; color: #6B7280; margin-top: 4px; }

    /* Info box */
    .info-row { display: table; width: 100%; margin-bottom: 12px; border: 1px solid #E5E7EB; border-radius: 4px; background: #F9FAFB; }
    .info-cell { display: table-cell; padding: 6px 12px; font-size: 9px; border-right: 1px solid #E5E7EB; }
    .info-cell:last-child { border-right: none; }
    .info-cell .label { color: #9CA3AF; margin-bottom: 2px; }
    .info-cell .value { font-weight: bold; color: #1F2937; font-size: 10px; }

    /* Tabel data */
    table.data { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.data thead tr { background: #1E3A5F; color: #fff; }
    table.data thead th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: bold; letter-spacing: 0.3px; }
    table.data tbody tr:nth-child(even) { background: #F8FAFC; }
    table.data tbody tr:nth-child(odd)  { background: #fff; }
    table.data tbody td { padding: 6px 8px; font-size: 9px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
    table.data tbody tr:last-child td { border-bottom: none; }

    /* Badge status */
    .badge { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 8px; font-weight: bold; }
    .badge-green  { background: #DCFCE7; color: #166534; }
    .badge-amber  { background: #FEF3C7; color: #92400E; }
    .badge-red    { background: #FEE2E2; color: #991B1B; }
    .badge-gray   { background: #F3F4F6; color: #374151; }
    .badge-blue   { background: #DBEAFE; color: #1E40AF; }

    /* Progress bar utilisasi */
    .progress-wrap { background: #E5E7EB; border-radius: 20px; height: 7px; width: 100%; margin-top: 3px; }
    .progress-bar  { border-radius: 20px; height: 7px; }

    /* Ringkasan bawah */
    .summary-box { border: 1px solid #E5E7EB; border-radius: 4px; padding: 8px 12px; background: #F9FAFB; margin-bottom: 12px; }
    .summary-box .summary-title { font-size: 10px; font-weight: bold; color: #374151; margin-bottom: 6px; }
    .summary-grid { display: table; width: 100%; }
    .summary-item { display: table-cell; text-align: center; padding: 4px; }
    .summary-item .num { font-size: 18px; font-weight: bold; color: #1E3A5F; }
    .summary-item .lbl { font-size: 8px; color: #9CA3AF; margin-top: 1px; }

    /* Footer */
    .footer { border-top: 1px solid #E5E7EB; padding-top: 8px; margin-top: 16px;
               display: table; width: 100%; font-size: 8px; color: #9CA3AF; }
    .footer-left  { display: table-cell; text-align: left; }
    .footer-right { display: table-cell; text-align: right; }
    .page-break { page-break-after: always; }
    .text-center { text-align: center; }
    .text-right  { text-align: right; }
    .fw-bold { font-weight: bold; }
    .text-blue { color: #1E40AF; }
</style>
</head>
<body>
    @yield('content')
</body>
</html>
