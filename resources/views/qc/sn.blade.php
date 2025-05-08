<div class="accordion" id="accordionExample">
    <div class="card">
        <div class="card-header" id="headingOne">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse"
                    data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    Lampiran
                </button>
            </h2>
        </div>

        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
            <div class="card-body">
                @include('qc.lampiran')
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header" id="headingTwo">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse"
                    data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    Table
                </button>
            </h2>
        </div>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
            <div class="card-body">
                @include('qc.table')
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header" id="headingThree">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse"
                    data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Tools
                </button>
            </h2>
        </div>
        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-12">
                        <label for="import">IMPORT FROM TEXT</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <button type="button" id="btn_import_clear" class="input-group-text">X</button>
                            </div>
                            <textarea name="import" id="import" class="form-control" rows="5"></textarea>
                            <div class="input-group-append">
                                <button type="button" id="btn_import" class="input-group-text">IMPORT</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-12">
                        <table id="table_tool" class="table table-sm table-hover" style="width: 100%;cursor: pointer;">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="width: 30px">#</th>
                                    <th>SN</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <div class="card-footer">
                <button type="button" id="btn_tool_simpan" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<br>
<br>
<br>
