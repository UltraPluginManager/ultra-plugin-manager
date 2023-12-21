
                    <div class="modal fade" tabindex="-1" id="upload-zip-modal" data-bs-keyboard="false">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mw-800px">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Upload Zip</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <form id="form-zip-upload" method="POST" action="" enctype="multipart/form-data">
                                        <div class="mb-4">
                                            <label for="zipFile" class="form-label">Add a zip file</label>
                                            <input multiple class="form-control" type="file" id="zipFile" name="zipFile[]" accept=".zip">
                                        </div>
                                        <button type="submit" id="start-zip-upload" name="start-zip-upload"
                                        class="btn btn-sm btn-dark mr-2 mt-3" data-bs-dismiss="modal" disabled>Upload</button>
                                        <span id="zip-process-message"></span>
                                        <input type="hidden" name="sec" value="<?php echo ultrapm_decrypt(file_get_contents(ULTRAPM_SECFILE));?>"/>
                                        <input id="zipFileName" type="hidden" name="zipFileName" value=""/>
                                    </form>                                          
                                </div>
                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div> -->
                            </div>
                        </div>
                    </div>


<script>
    $('#zipFile').change(function() {
        var zipFileName = $('#zipFile').val().split('\\').pop();
        $('#zipFileName').val(zipFileName);
    });
    $('#zipFile').on('change', function() {
        if ($('#zipFile').val()) {
            $('#start-zip-upload').prop('disabled', false);
        }
    });
</script>