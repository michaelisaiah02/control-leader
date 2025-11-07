<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Print Report</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />
  {{-- @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js']) --}}
  <style>
    /* Pastikan hanya style yang perlu dipakai di print */
    @page {
      size: A4;
      orientation: landscape !important;
      margin: 10mm;
    }

    body {
      font-family: Arial, sans-serif;
      font-size: 12pt;
      margin: 0;
      padding: 0;
    }

    .table {
      width: 100%;
      border-collapse: collapse;
    }

    .table,
    .table th,
    .table td {
      border: 1px solid #000;
    }

    th,
    td {
      padding: 4px;
      text-align: center;
    }

    thead th {
      background: #eee;
    }

    .no-break {
      page-break-inside: avoid;
    }

    .signature-area-wrapper {
      position: relative;
      /* Ini yang akan mengurung si Buzz Lightyear */
      /* Pastikan lebarnya sesuai dengan tabelnya */
      width: 40%;
      float: right;
      /* atau margin-left: auto; untuk membuatnya di kanan */
    }

    .already-sign-overlay {
      display: none;
      position: absolute;

      /* Posisikan di tengah-tengah area TTD */
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      /* Trik centering modern */

      width: 95%;
      /* Sedikit lebih kecil dari kandang biar ada napas */
      background-color: white;
      border: 2px solid black;
      font-weight: bold;
      text-align: center;
      padding: 8px 0;
      z-index: 10;
    }

    /* Overlay khusus preview */
    .preview-overlay {
      font-weight: bold;
      border: 2px solid #000;
      background: white;
      display: inline-block;
      padding: 4px 8px;
      margin-top: 4px;
    }

    /* Hide print button during printing */
    @media print {

      button.print-button,
      button.back-button,
      .sign-checkbox-container,
      .preview-checkbox-container {
        display: none !important;
      }

      .already-sign-overlay {
        display: none;
      }

      @page {
        size: A4;
        orientation: landscape !important;
        /* atau bisa pakai: size: landscape; */
        margin: 1cm;
      }

      body {
        margin: 0;
        padding: 0;
      }

      body.show-sign-on-print .already-sign-overlay {
        display: block;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid py-0 px-1">
    <div class="row justify-content-center" style="margin-bottom: 3px;">
      <table class="table table-sm table-bordered align-middle">
        <thead>
          <tr>
            <th scope="col" colspan="1" rowspan="3" class="text-center align-middle" style="width: 50px;">
              <div class="d-grid gap-1 h-100 fs-5 fw-medium"
                style="display: grid; justify-content: center; font-size: 10px; font-weight: 500; gap: 5px;">
                <img src="{{ asset('image/logo-pt.png') }}" alt="Logo" width="30px" class="mx-auto"
                  style="margin-left: auto; margin-right: auto;">
                PT.CAR
              </div>
            </th>
            <th scope="col" colspan="5" rowspan="3" class="text-center align-middle fs-4"
              style="font-size: 20pt;">
              VERIFIKASI HASIL KALIBRASI</th>
            <th scope="col">NO.FORM</th>
            <th scope="col">:</th>
            <th scope="col">FR-QAC-098</th>
          </tr>
          <tr>
            <th scope="col">TGL.FORM</th>
            <th scope="col">:</th>
            <th scope="col">11/01/2023</th>
          </tr>
          <tr>
            <th scope="col">Revisi</th>
            <th scope="col">:</th>
            <th scope="col">0</th>
          </tr>
          <tr>
            <th scope="col" colspan="2">Equipment Name</th>
            <th scope="col" colspan="4">{{ $result->masterList->equipment->name }}</th>
            <th scope="col">Merk</th>
            <th scope="col">:</th>
            <th scope="col">{{ $result->masterList->brand }}</th>
          </tr>
          <tr>
            <th scope="col" colspan="2">No. ID</th>
            <th scope="col" colspan="4">{{ $result->id_num }}</th>
            <th scope="col">Cal Equipment</th>
            <th scope="col">:</th>
            <th scope="col">{{ $result->calibrator_equipment ?? 'N/A' }}</th>
          </tr>
          <tr>
            <th scope="col" colspan="2">Calibration Date</th>
            <th scope="col" colspan="4">
              {{ $result->calibration_date->locale('id')->translatedFormat('d F Y') }}</th>
            <th scope="col">Std Keberterimaan</th>
            <th scope="col">:</th>
            <th scope="col">{{ $result->masterList->acceptance_criteria }}</th>
          </tr>
          <tr>
            <th scope="col" style="width: 15px;">No</th>
            <th scope="col" colspan="2">Standard Indication
              ({{ optional($result->masterList->unit)->symbol ?? 'N/A' }})
            </th>
            <th scope="col" colspan="2">Actual Indication
              ({{ optional($result->masterList->unit)->symbol ?? 'N/A' }})
            </th>
            <th scope="col">Correction ({{ optional($result->masterList->unit)->symbol ?? 'N/A' }})</th>
            <th scope="col" colspan="3">Judgment</th>
          </tr>
        </thead>
        <tbody>
          @for ($i = 1; $i <= 10; $i++)
            @php
              $key = 'param_' . str_pad($i, 2, '0', STR_PAD_LEFT);
              $std = $result->masterList->standard->$key;
              $val = $result->$key;
            @endphp
            <tr>
              <th scope="row">{{ $i }}</th>
              @if ($std == 99999)
                <td colspan="2">-</td>
                <td colspan="2">-</td>
                <td>-</td>
              @else
                <td colspan="2">{{ floatval($std) }}</td>
                <td colspan="2">{{ floatval($val) }}</td>
                <td>{{ floatval($val - $std) }}</td>
              @endif

              @if ($i == 1)
                <td colspan="3" rowspan="10"
                  style="{{ $result->judgement == 'Disposal' ? 'font-size: 4rem;' : 'font-size: 10rem;' }} font-weight: 100">
                  {{ $result->judgement }}
                </td>
              @endif
            </tr>
          @endfor
        </tbody>
      </table>
    </div>
    <div class="signature-area-wrapper">

      <div class="already-sign-overlay">ALREADY SIGN</div>

      <div class="row justify-content-end" style="display: flex; justify-content: end;">
        <div class="col-4" style="width: 100%;">
          <table class="table table-bordered text-center" id="signature-table">
            <thead>
              <tr>
                <th scope="col" style="width: 33%;">Disetujui</th>
                <th scope="col" style="width: 33%;">Diperiksa</th>
                <th scope="col" style="width: 33%;">Dibuat</th>
              </tr>
              <tr class="signature-space-row">
                <th scope="col" style="height: 50px;" id="approved-cell">&nbsp;</th>
                <th scope="col" style="height: 50px;" id="checked-cell">&nbsp;</th>
                <th scope="col" style="height: 50px;">&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              <tr class="signature-names-row">
                <td id="approved-name">{{ $approved->name }}</td>
                <td id="checked-name">{{ $checked->name }}</td>
                <td>{{ $result->creator->name }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const currentUser = "{{ auth()->user()->name }}";
      // --- MEMBUAT CHECKBOX ---
      const signCheckboxContainer = document.createElement('div');
      signCheckboxContainer.classList.add('sign-checkbox-container');
      signCheckboxContainer.style.position = 'fixed';
      signCheckboxContainer.style.bottom = '20px';
      signCheckboxContainer.style.left = '20px';
      signCheckboxContainer.innerHTML = `
            <input type="checkbox" id="already-sign-checkbox" style="margin-right: 5px;">
            <label for="already-sign-checkbox" style="color: #333; font-size: 14px;">Already Sign Print</label>
        `;
      document.body.appendChild(signCheckboxContainer);

      // --- EVENT LISTENER UNTUK CHECKBOX ---
      const alreadySignCheckbox = document.getElementById('already-sign-checkbox');
      alreadySignCheckbox.addEventListener('change', function() {
        if (this.checked) {
          // Jika dicentang, tambahkan class ke body
          document.body.classList.add('show-sign-on-print');
        } else {
          // Jika tidak dicentang, hapus class dari body
          document.body.classList.remove('show-sign-on-print');
        }
      });

      // === CHECKBOX: Preview Disetujui & Diperiksa ===
      const previewCheckboxContainer = document.createElement('div');
      previewCheckboxContainer.classList.add('preview-checkbox-container');
      previewCheckboxContainer.style.position = 'fixed';
      previewCheckboxContainer.style.bottom = '60px';
      previewCheckboxContainer.style.left = '20px';
      previewCheckboxContainer.innerHTML = `
            <div style="margin-bottom:5px;">
                <input type="checkbox" id="preview-approved-checkbox" style="margin-right: 5px;">
                <label for="preview-approved-checkbox" style="color: #333; font-size: 14px;">Preview Disetujui</label>
            </div>
            <div>
                <input type="checkbox" id="preview-checked-checkbox" style="margin-right: 5px;">
                <label for="preview-checked-checkbox" style="color: #333; font-size: 14px;">Preview Diperiksa</label>
            </div>
        `;
      document.body.appendChild(previewCheckboxContainer);

      const approvedCheckbox = document.getElementById('preview-approved-checkbox');
      const checkedCheckbox = document.getElementById('preview-checked-checkbox');
      const approvedCell = document.getElementById('approved-cell');
      const checkedCell = document.getElementById('checked-cell');
      const approvedName = document.getElementById('approved-name');
      const checkedName = document.getElementById('checked-name');

      // Simpan nama original
      const originalApprovedName = approvedName.textContent;
      const originalCheckedName = checkedName.textContent;

      approvedCheckbox.addEventListener('change', function() {
        if (this.checked) {
          approvedCell.innerHTML = `<div class="preview-overlay">ALREADY APPROVED</div>`;
          approvedName.textContent = currentUser;
        } else {
          approvedCell.innerHTML = "&nbsp;";
          approvedName.textContent = originalApprovedName;
        }
      });

      checkedCheckbox.addEventListener('change', function() {
        if (this.checked) {
          checkedCell.innerHTML = `<div class="preview-overlay">ALREADY CHECKED</div>`;
          checkedName.textContent = currentUser;
        } else {
          checkedCell.innerHTML = "&nbsp;";
          checkedName.textContent = originalCheckedName;
        }
      });

      // --- KODE TOMBOL BACK & PRINT KAMU (TETAP SAMA) ---
      const printButton = document.createElement('button');
      printButton.textContent = 'Print';
      printButton.style.position = 'fixed';
      printButton.style.bottom = '20px';
      printButton.style.left = '50%';
      printButton.style.transform = 'translateX(-50%)';
      printButton.classList.add('print-button');
      // (style lainnya tetap sama)
      printButton.style.padding = '10px 20px';
      printButton.style.backgroundColor = '#181d3d';
      printButton.style.color = '#fff';
      printButton.style.border = 'none';
      printButton.style.borderRadius = '5px';
      printButton.style.cursor = 'pointer';

      printButton.addEventListener('click', function() {
        window.print();
        setTimeout(() => {
          window.location.href = "{{ route('kalibrasi.report.menu') }}";
        }, 1000);
      });
      document.body.appendChild(printButton);

      const backButton = document.createElement('button');
      backButton.textContent = 'Back';
      backButton.style.position = 'fixed';
      backButton.style.bottom = '20px';
      backButton.style.left = '50%';
      backButton.style.transform = 'translateX(-180%)';
      backButton.classList.add('back-button');
      // (style lainnya tetap sama)
      backButton.style.padding = '10px 20px';
      backButton.style.backgroundColor = '#181d3d';
      backButton.style.color = '#fff';
      backButton.style.border = 'none';
      backButton.style.borderRadius = '5px';
      backButton.style.cursor = 'pointer';

      backButton.addEventListener('click', function() {
        setTimeout(() => {
          window.location.href = "{{ route('kalibrasi.report.menu') }}";
        }, 1000);
      });
      document.body.appendChild(backButton);
    });
  </script>
</body>

</html>
