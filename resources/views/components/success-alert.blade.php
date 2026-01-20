<div class="modal fade" id="globalSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 p-4 text-center">

            {{-- ICON --}}
            <div class="mx-auto mb-3">
                <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center"
                     style="width:72px; height:72px;">
                    <i class="bi bi-check-circle-fill fs-2"></i>
                </div>
            </div>

            {{-- TITLE --}}
            <h4 class="fw-semibold mb-2" id="successTitle">
                Berhasil
            </h4>

            {{-- MESSAGE --}}
            <p class="text-muted mb-4 px-3" id="successMessage">
                Data berhasil diproses.
            </p>

            {{-- ACTIONS --}}
            <div class="d-flex justify-content-center">
                <button type="button"
                        class="btn btn-primary px-4 d-flex align-items-center gap-2"
                        data-bs-dismiss="modal">
                    Oke
                </button>
            </div>

        </div>
    </div>
</div>
