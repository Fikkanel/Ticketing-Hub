@extends('layouts.public')

@section('title', 'E-Ticket - ' . $ticket->orderItem->product->event->judul)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                {{-- Header Branding --}}
                @php
                    $settings = \App\Models\Setting::all()->pluck('value', 'key');
                    $headerBg = $settings['header_bg_color'] ?? '#1a365d';
                    $headerText = $settings['header_text_color'] ?? '#ffffff';
                @endphp
                <div class="p-3 text-center" style="background-color: {{ $headerBg }}; color: {{ $headerText }};">
                    <h5 class="mb-0 fw-bold">E-TICKET</h5>
                </div>

                <div class="card-body p-0">
                    {{-- Banner --}}
                    <div class="ratio ratio-21x9">
                        <img src="{{ $ticket->orderItem->product->event->banner_src }}" class="object-fit-cover" alt="Event Banner">
                    </div>

                    <div class="p-4">
                        <div class="row align-items-center mb-4">
                            <div class="col-lg-8">
                                <h3 class="fw-bold mb-1">{{ $ticket->orderItem->product->event->judul }}</h3>
                                <p class="text-muted mb-0">
                                    <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">
                                        {{ $ticket->orderItem->product->nama_produk }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <div class="bg-light p-3 rounded-3 border">
                                    <small class="text-muted d-block text-uppercase fw-bold mb-1" style="font-size: 10px;">Ticket Code</small>
                                    <span class="fw-bold font-monospace text-primary h5 mb-0">{{ $ticket->ticket_code }}</span>
                                </div>
                            </div>
                        </div>

                        <hr class="opacity-10">

                        <div class="row g-4">
                            <div class="col-md-7">
                                <div class="row g-4">
                                    <div class="col-6">
                                        <label class="small text-muted text-uppercase fw-bold d-block mb-1">Date</label>
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($ticket->orderItem->product->event->tgl_mulai)->isoFormat('D MMM YYYY') }}</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="small text-muted text-uppercase fw-bold d-block mb-1">Time</label>
                                        <span class="fw-semibold">{{ \Carbon\Carbon::parse($ticket->orderItem->product->event->tgl_mulai)->format('H:i') }} WIB</span>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted text-uppercase fw-bold d-block mb-1">Venue</label>
                                        <span class="fw-semibold">{{ $ticket->orderItem->product->event->location->nama_lokasi }}</span>
                                        <small class="d-block text-muted">{{ $ticket->orderItem->product->event->location->alamat }}</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted text-uppercase fw-bold d-block mb-1">Holder Name</label>
                                        <span class="fw-semibold">{{ $ticket->orderItem->order->customer->name }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5 text-center">
                                <div class="bg-white p-3 rounded-4 shadow-sm border d-inline-block">
                                    {!! QrCode::size(180)->generate($ticket->ticket_code) !!}
                                    <p class="mt-3 mb-0 small text-muted fw-bold">SCAN ME FOR CHECK-IN</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-light rounded-4 border border-dashed">
                            <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2"></i> Important Information</h6>
                            <ul class="small text-muted mb-0 ps-3">
                                <li class="mb-2">Show this digital ticket at the entrance for scanning.</li>
                                <li class="mb-2">This ticket is valid for one-time entry only.</li>
                                <li class="mb-2">Do not share this link or ticket code with anyone.</li>
                                <li>The organizer is not responsible for any duplicated tickets.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white p-4 border-top-0 text-center">
                    <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-print me-2"></i> Print or Save as PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
    .border-dashed { border-style: dashed !important; }
    @media print {
        header, footer, .btn, .no-print { display: none !important; }
        .container { max-width: 100% !important; width: 100% !important; padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection
