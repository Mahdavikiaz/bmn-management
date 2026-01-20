<div class="modal fade" id="globalDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 p-4 text-center">

            {{-- ICON --}}
            <div class="mx-auto mb-3">
                <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center"
                     style="width:72px; height:72px;">
                    <i class="bi bi-exclamation-triangle-fill fs-2"></i>
                </div>
            </div>

            {{-- TITLE --}}
            <h4 class="fw-semibold mb-2" id="deleteTitle">
                Delete data?
            </h4>

            {{-- MESSAGE --}}
            <p class="text-muted mb-4 px-3" id="deleteMessage">
                Are you sure you want to delete this data?
            </p>

            {{-- ACTIONS --}}
            <div class="d-flex justify-content-center gap-3">
                <button type="button"
                        class="btn btn-light px-4"
                        data-bs-dismiss="modal">
                    Batal
                </button>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-danger px-4 d-flex align-items-center gap-2">
                        <i class="bi bi-trash"></i>
                        Ya, Hapus
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
