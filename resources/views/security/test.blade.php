{{--
|==============================================================================
| Security Testing Dashboard - Shared (All Students)
|==============================================================================
|
| @author     Ng Wayne Xiang, Haerine Deepak Singh, Low Nam Lee, Lee Song Yan, Lee Kin Hang
| @module     Security Testing
|
| OWASP security testing dashboard for verifying security controls.
| Used for testing authentication, authorization, and input validation.
|==============================================================================
--}}

@extends('layouts.app')

@section('title', 'Security Testing Dashboard')

@push('styles')
<style>
    .test-card {
        border-radius: 16px;
        transition: all 0.2s ease;
    }
    .test-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .student-badge {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 600;
    }
    .student-1 { background: #FFE5E5; color: #D63031; }
    .student-2 { background: #E5F3FF; color: #0984E3; }
    .student-3 { background: #E5FFE5; color: #00B894; }
    .student-4 { background: #FFF5E5; color: #E17055; }
    .student-5 { background: #F5E5FF; color: #6C5CE7; }
    .owasp-tag {
        font-size: 0.7rem;
        background: #f8f9fa;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: monospace;
    }
    .result-box {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 16px;
        font-family: 'Monaco', 'Menlo', monospace;
        font-size: 0.85rem;
        max-height: 400px;
        overflow-y: auto;
    }
    .status-pass { color: #00B894; }
    .status-fail { color: #D63031; }
    .status-warn { color: #FDCB6E; }
    .btn-test {
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .test-section {
        margin-bottom: 2rem;
    }
    .test-section h5 {
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>
@endpush

@section('content')
<div class="page-content">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 style="font-weight: 700; letter-spacing: -0.5px;" class="mb-1">
                <i class="bi bi-shield-check me-2" style="color: var(--success-color);"></i>Security Testing Dashboard
            </h2>
            <p class="text-muted mb-0">Test OWASP security practices implementation for each student module</p>
        </div>
        <span class="badge bg-warning text-dark" style="font-size: 0.85rem; padding: 8px 14px;">
            <i class="bi bi-exclamation-triangle me-1"></i> Development Only
        </span>
    </div>

    @if(app()->environment('production'))
    <div class="alert alert-danger">
        <i class="bi bi-shield-x me-2"></i>
        <strong>Access Denied:</strong> Security testing is disabled in production environment.
    </div>
    @else

    <div class="row g-4">
        <!-- Ng Wayne Xiang: Authentication & Session -->
        <div class="col-12">
            <div class="test-section">
                <h5>
                    <span class="student-badge student-1">Ng Wayne Xiang</span>
                    User & Authentication Module
                </h5>
                <div class="row g-3">
                    <!-- Rate Limiting Test -->
                    <div class="col-md-4">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">Rate Limiting</h6>
                                    <span class="owasp-tag">OWASP 41, 94</span>
                                </div>
                                <p class="text-muted small mb-3">Test brute force protection (5 attempts max)</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('rate-limiting')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <button class="btn btn-outline-secondary btn-test w-100 mt-2" onclick="clearRateLimit()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Counter
                                </button>
                                <div id="result-rate-limiting" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Generic Errors Test -->
                    <div class="col-md-4">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">Generic Error Messages</h6>
                                    <span class="owasp-tag">OWASP 33</span>
                                </div>
                                <p class="text-muted small mb-3">Verify login errors don't reveal user existence</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('generic-errors')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-generic-errors" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Session Security Test -->
                    <div class="col-md-4">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">Session Security</h6>
                                    <span class="owasp-tag">OWASP 64-76</span>
                                </div>
                                <p class="text-muted small mb-3">Check session config (encryption, cookies, expiry)</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('session-security')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-session-security" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Haerine Deepak Singh: Menu & Catalog -->
        <div class="col-12">
            <div class="test-section">
                <h5>
                    <span class="student-badge student-2">Haerine Deepak Singh</span>
                    Menu & Catalog Module
                </h5>
                <div class="row g-3">
                    <!-- SQL Injection Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">SQL Injection Prevention</h6>
                                    <span class="owasp-tag">OWASP 167-168</span>
                                </div>
                                <p class="text-muted small mb-3">Test parameterized queries against malicious inputs</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('sql-injection')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-sql-injection" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- XSS Prevention Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">XSS Prevention</h6>
                                    <span class="owasp-tag">OWASP 17-20</span>
                                </div>
                                <p class="text-muted small mb-3">Test output encoding for script injection</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('xss-prevention')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-xss-prevention" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Nam Lee: Cart & Checkout -->
        <div class="col-12">
            <div class="test-section">
                <h5>
                    <span class="student-badge student-3">Low Nam Lee</span>
                    Cart & Checkout Module
                </h5>
                <div class="row g-3">
                    <!-- CSRF Protection Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">CSRF Protection</h6>
                                    <span class="owasp-tag">OWASP 73</span>
                                </div>
                                <p class="text-muted small mb-3">Verify CSRF tokens are generated and validated</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('csrf-protection')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-csrf-protection" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Validation Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">Server-Side Price Validation</h6>
                                    <span class="owasp-tag">OWASP 1</span>
                                </div>
                                <p class="text-muted small mb-3">Test that prices cannot be manipulated by client</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('price-validation')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-price-validation" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lee Song Yan: Order & Pickup -->
        <div class="col-12">
            <div class="test-section">
                <h5>
                    <span class="student-badge student-4">Lee Song Yan</span>
                    Order & Pickup Module
                </h5>
                <div class="row g-3">
                    <!-- IDOR Prevention Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">IDOR Prevention</h6>
                                    <span class="owasp-tag">OWASP 86</span>
                                </div>
                                <p class="text-muted small mb-3">Test authorization checks on order access</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('idor-prevention')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-idor-prevention" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Signature Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">QR Code Digital Signature</h6>
                                    <span class="owasp-tag">OWASP 104</span>
                                </div>
                                <p class="text-muted small mb-3">Test HMAC-SHA256 signature verification</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('qr-signature')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-qr-signature" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lee Kin Hang: Vouchers & Notifications -->
        <div class="col-12">
            <div class="test-section">
                <h5>
                    <span class="student-badge student-5">Lee Kin Hang</span>
                    Vouchers & Notifications Module
                </h5>
                <div class="row g-3">
                    <!-- Voucher Generation Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">Cryptographic Voucher Codes</h6>
                                    <span class="owasp-tag">OWASP 104</span>
                                </div>
                                <p class="text-muted small mb-3">Test CSPRNG voucher code generation</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('voucher-generation')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-voucher-generation" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Logging Test -->
                    <div class="col-md-6">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">Audit Logging</h6>
                                    <span class="owasp-tag">OWASP 119, 124</span>
                                </div>
                                <p class="text-muted small mb-3">Test point transaction audit trail</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('audit-logging')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-audit-logging" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Tests -->
        <div class="col-12">
            <div class="test-section">
                <h5>
                    <span class="badge bg-dark" style="font-size: 0.75rem; padding: 4px 10px; border-radius: 20px;">All Students</span>
                    Global Security Configuration
                </h5>
                <div class="row g-3">
                    <!-- CORS Config Test -->
                    <div class="col-md-4">
                        <div class="card test-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0" style="font-weight: 600;">CORS Configuration</h6>
                                    <span class="owasp-tag">OWASP 143-150</span>
                                </div>
                                <p class="text-muted small mb-3">Test Cross-Origin Resource Sharing settings</p>
                                <button class="btn btn-outline-primary btn-test w-100" onclick="runTest('cors-config')">
                                    <i class="bi bi-play-fill me-1"></i> Run Test
                                </button>
                                <div id="result-cors-config" class="result-box mt-3 d-none"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Run All Tests -->
                    <div class="col-md-8">
                        <div class="card test-card h-100 border-primary">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <h6 class="mb-2" style="font-weight: 600;"><i class="bi bi-lightning-charge me-2"></i>Run All Tests</h6>
                                <p class="text-muted small mb-3">Execute all security tests at once and view summary</p>
                                <button class="btn btn-primary btn-test" onclick="runAllTests()">
                                    <i class="bi bi-play-circle me-1"></i> Run All Security Tests
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Summary Modal -->
    <div class="modal fade" id="summaryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-clipboard-check me-2"></i>Security Test Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="summaryContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted">Running tests...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
</div>
@endsection

@push('scripts')
<script>
const testEndpoints = {
    'rate-limiting': '/security-test/rate-limiting',
    'generic-errors': '/security-test/generic-errors',
    'session-security': '/security-test/session-security',
    'sql-injection': '/security-test/sql-injection',
    'xss-prevention': '/security-test/xss-prevention',
    'csrf-protection': '/security-test/csrf-protection',
    'price-validation': '/security-test/price-validation',
    'idor-prevention': '/security-test/idor-prevention',
    'qr-signature': '/security-test/qr-signature',
    'voucher-generation': '/security-test/voucher-generation',
    'audit-logging': '/security-test/audit-logging',
    'cors-config': '/security-test/cors-config',
};

function runTest(testId) {
    const resultBox = document.getElementById('result-' + testId);
    resultBox.classList.remove('d-none');
    resultBox.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Running...</div>';
    
    fetch(testEndpoints[testId], {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        resultBox.innerHTML = formatResult(data);
    })
    .catch(err => {
        resultBox.innerHTML = '<span class="status-fail">Error: ' + err.message + '</span>';
    });
}

function formatResult(data) {
    let html = '<div class="mb-2">';
    html += '<strong>' + data.test + '</strong>';
    html += ' <span class="status-' + (data.status || data.overall_status || 'pass').toLowerCase() + '">[' + (data.status || data.overall_status || 'PASS') + ']</span>';
    html += '</div>';
    
    if (data.explanation) {
        html += '<div class="small text-muted mb-2">' + data.explanation + '</div>';
    }
    
    html += '<pre style="font-size: 0.75rem; margin: 0; white-space: pre-wrap;">' + JSON.stringify(data, null, 2) + '</pre>';
    return html;
}

function clearRateLimit() {
    fetch('/security-test/clear-rate-limit', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        showToast('Rate limit counter reset', 'success');
    });
}

async function runAllTests() {
    const modal = new bootstrap.Modal(document.getElementById('summaryModal'));
    modal.show();
    
    const summaryContent = document.getElementById('summaryContent');
    let results = [];
    let passed = 0;
    let failed = 0;
    
    for (const [testId, endpoint] of Object.entries(testEndpoints)) {
        try {
            const res = await fetch(endpoint, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();
            const status = (data.status || data.overall_status || 'PASS').toUpperCase();
            results.push({ test: data.test, student: data.student, status: status });
            if (status === 'PASS') passed++; else failed++;
        } catch (err) {
            results.push({ test: testId, status: 'ERROR' });
            failed++;
        }
    }
    
    let html = '<div class="alert ' + (failed === 0 ? 'alert-success' : 'alert-warning') + '">';
    html += '<strong>' + passed + '/' + (passed + failed) + ' tests passed</strong>';
    html += '</div>';
    
    html += '<table class="table table-sm">';
    html += '<thead><tr><th>Test</th><th>Student</th><th>Status</th></tr></thead><tbody>';
    results.forEach(r => {
        const statusClass = r.status === 'PASS' ? 'success' : (r.status === 'WARN' ? 'warning' : 'danger');
        html += '<tr>';
        html += '<td>' + r.test + '</td>';
        html += '<td>' + (r.student || '-') + '</td>';
        html += '<td><span class="badge bg-' + statusClass + '">' + r.status + '</span></td>';
        html += '</tr>';
    });
    html += '</tbody></table>';
    
    summaryContent.innerHTML = html;
}
</script>
@endpush
