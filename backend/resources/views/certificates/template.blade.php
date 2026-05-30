<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Certificat {{ $certificate->certificate_number }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0a2540;
            background: #f6f9fc;
        }
        .page {
            width: 100%;
            height: 100%;
            padding: 42px;
            background: linear-gradient(135deg, #ffffff 0%, #f6f9fc 55%, #eaf4ff 100%);
        }
        .certificate {
            height: 511px;
            border: 1px solid #dbe5f0;
            border-radius: 28px;
            background: #ffffff;
            padding: 42px 48px;
            position: relative;
        }
        .mark {
            position: absolute;
            right: 48px;
            top: 42px;
            color: #0071e3;
            font-weight: 700;
            letter-spacing: .12em;
            font-size: 14px;
        }
        .eyebrow {
            color: #5f6f86;
            font-size: 12px;
            letter-spacing: .18em;
            text-transform: uppercase;
            margin: 0 0 28px;
        }
        h1 {
            margin: 0;
            font-size: 44px;
            line-height: 1.06;
            letter-spacing: -.05em;
            max-width: 640px;
        }
        .recipient {
            margin: 34px 0 8px;
            color: #5f6f86;
            font-size: 14px;
        }
        .name {
            margin: 0;
            font-size: 34px;
            letter-spacing: -.03em;
            font-weight: 700;
        }
        .course {
            width: 68%;
            margin-top: 26px;
            font-size: 16px;
            line-height: 1.65;
            color: #36465d;
        }
        .course strong { color: #0a2540; }
        .meta {
            position: absolute;
            left: 48px;
            right: 48px;
            bottom: 36px;
            border-top: 1px solid #dbe5f0;
            padding-top: 22px;
            display: table;
            width: calc(100% - 96px);
        }
        .meta-col {
            display: table-cell;
            vertical-align: bottom;
        }
        .label {
            color: #5f6f86;
            font-size: 10px;
            letter-spacing: .13em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .value { font-size: 13px; font-weight: 700; }
        .qr {
            width: 118px;
            text-align: right;
        }
        .qr img { width: 92px; height: 92px; }
        .verify {
            margin-top: 5px;
            font-size: 8px;
            color: #5f6f86;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="certificate">
            <div class="mark">ED-TECH</div>
            <p class="eyebrow">Certificat de réussite vérifiable</p>
            <h1>Certification automatisée de fin de parcours</h1>

            <p class="recipient">Ce certificat est décerné à</p>
            <p class="name">{{ $user->name }}</p>

            <p class="course">
                Pour avoir terminé avec succès <strong>100% des leçons</strong> et validé tous les quiz obligatoires du cours
                <strong>{{ $course->title }}</strong>.
            </p>

            <div class="meta">
                <div class="meta-col">
                    <div class="label">Numéro</div>
                    <div class="value">{{ $certificate->certificate_number }}</div>
                </div>
                <div class="meta-col">
                    <div class="label">Date d’émission</div>
                    <div class="value">{{ $certificate->issued_at->format('d/m/Y') }}</div>
                </div>
                <div class="meta-col">
                    <div class="label">Formateur</div>
                    <div class="value">{{ $course->instructor?->name ?? 'Équipe pédagogique ED-TECH' }}</div>
                </div>
                <div class="meta-col qr">
                    <img alt="QR Code de vérification" src="data:image/svg+xml;base64,{{ base64_encode($qrCodeSvg) }}">
                    <div class="verify">{{ $verificationUrl }}</div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
