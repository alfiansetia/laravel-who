<script>
    let itemIndex = {{ isset($estimate) ? $estimate->items->count() : 1 }};
    let packageIndex = {{ isset($estimate) ? $estimate->packages->count() : 1 }};
    let rateIndex = {{ isset($estimate) ? $estimate->rates->count() : 1 }};

    // Initialize on page load
    $(document).ready(function() {
        initCurrencyMask();
        calculateTotalInvoice();
        calculateAllDimensions();
    });

    // Currency mask
    function initCurrencyMask() {
        $('.currency').each(function() {
            $(this).on('keyup', function(e) {
                let value = $(this).val().replace(/\D/g, '');
                $(this).val(formatNumber(value));
            });
        });
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseNumber(str) {
        if (!str) return 0;
        return parseFloat(str.toString().replace(/\./g, '').replace(',', '.')) || 0;
    }

    // Items
    function addItem() {
        let html = `
            <tr>
                <td class="text-center item-no">${itemIndex + 1}</td>
                <td><input type="text" class="form-control form-control-sm" name="items[${itemIndex}][item_name]" required></td>
                <td><input type="number" class="form-control form-control-sm item-qty" name="items[${itemIndex}][quantity]" value="1" min="1" onchange="calculateTotalInvoice()"></td>
                <td><input type="text" class="form-control form-control-sm item-total currency" name="items[${itemIndex}][total_price]" value="0" onchange="calculateTotalInvoice()"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#itemsBody').append(html);
        itemIndex++;
        initCurrencyMask();
        renumberItems();
    }



    function calculateTotalInvoice() {
        let totalQty = 0;
        let totalPrice = 0;
        $('#itemsBody tr').each(function() {
            totalQty += parseInt($(this).find('.item-qty').val()) || 0;
            totalPrice += parseNumber($(this).find('.item-total').val());
        });
        $('#totalQty').val(totalQty + ' pcs');
        $('#totalInvoice').val(formatNumber(totalPrice.toFixed(0)));
    }

    function renumberItems() {
        $('#itemsBody tr').each(function(i) {
            $(this).find('.item-no').text(i + 1);
        });
    }

    // Packages
    function addPackage() {
        let html = `
            <tr>
                <td class="text-center pkg-no">${packageIndex + 1}</td>
                <td><input type="text" class="form-control form-control-sm" name="packages[${packageIndex}][package_name]"></td>
                <td><input type="number" class="form-control form-control-sm pkg-qty" name="packages[${packageIndex}][quantity]" value="1" min="1" onchange="calculateDimension(this)"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm pkg-weight" name="packages[${packageIndex}][weight_actual]" value="0" onchange="calculateDimension(this)"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm pkg-length" name="packages[${packageIndex}][dimension_length]" value="0" onchange="calculateDimension(this)"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm pkg-width" name="packages[${packageIndex}][dimension_width]" value="0" onchange="calculateDimension(this)"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm pkg-height" name="packages[${packageIndex}][dimension_height]" value="0" onchange="calculateDimension(this)"></td>
                <td><input type="text" class="form-control form-control-sm pkg-dim-reg" readonly></td>
                <td><input type="text" class="form-control form-control-sm pkg-dim-darat" readonly></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#packagesBody').append(html);
        packageIndex++;
        renumberPackages();
    }

    function calculateDimension(el) {
        let row = $(el).closest('tr');
        let length = parseFloat(row.find('.pkg-length').val()) || 0;
        let width = parseFloat(row.find('.pkg-width').val()) || 0;
        let height = parseFloat(row.find('.pkg-height').val()) || 0;

        let dimReg = Math.ceil((length * width * height) / 6000);
        let dimDarat = Math.ceil((length * width * height) / 4000);

        row.find('.pkg-dim-reg').val(dimReg);
        row.find('.pkg-dim-darat').val(dimDarat);

        calculateTotalWeight();
    }

    function calculateAllDimensions() {
        $('#packagesBody tr').each(function() {
            let length = parseFloat($(this).find('.pkg-length').val()) || 0;
            let width = parseFloat($(this).find('.pkg-width').val()) || 0;
            let height = parseFloat($(this).find('.pkg-height').val()) || 0;

            let dimReg = Math.ceil((length * width * height) / 6000);
            let dimDarat = Math.ceil((length * width * height) / 4000);

            $(this).find('.pkg-dim-reg').val(dimReg);
            $(this).find('.pkg-dim-darat').val(dimDarat);
        });
        calculateTotalWeight();
    }

    function calculateTotalWeight() {
        let totalKoli = 0;
        let totalWeight = 0;
        let totalDimReg = 0;
        let totalDimDarat = 0;

        $('#packagesBody tr').each(function() {
            let qty = parseInt($(this).find('.pkg-qty').val()) || 1;
            let weight = parseFloat($(this).find('.pkg-weight').val()) || 0;
            let dimReg = parseFloat($(this).find('.pkg-dim-reg').val()) || 0;
            let dimDarat = parseFloat($(this).find('.pkg-dim-darat').val()) || 0;

            totalKoli += qty;
            totalWeight += weight * qty;
            totalDimReg += dimReg * qty;
            totalDimDarat += dimDarat * qty;
        });

        $('#totalKoli').val(totalKoli + ' koli');
        $('#totalWeight').val(totalWeight.toFixed(2) + ' kg');
        $('#totalDimReg').val(totalDimReg.toFixed(2) + ' kg');
        $('#totalDimDarat').val(totalDimDarat.toFixed(2) + ' kg');
    }

    function renumberPackages() {
        $('#packagesBody tr').each(function(i) {
            $(this).find('.pkg-no').text(i + 1);
        });
    }

    // Rates
    function addRate() {
        let html = `
            <tr>
                <td class="text-center rate-no">${rateIndex + 1}</td>
                <td><input type="text" class="form-control form-control-sm" name="rates[${rateIndex}][shipper_name]" placeholder="TIKI, JNE, dll" required></td>
                <td>
                    <select class="form-control form-control-sm" name="rates[${rateIndex}][shipping_type]">
                        <option value="REG">REG/Udara</option>
                        <option value="DARAT" selected>DARAT/Laut</option>
                    </select>
                </td>
                <td><input type="text" class="form-control form-control-sm currency" name="rates[${rateIndex}][rate_per_kg]" value="0"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="rates[${rateIndex}][insurance_percentage]" value="0.20"></td>
                <td><input type="text" class="form-control form-control-sm currency" name="rates[${rateIndex}][packing_cost]" value="0"></td>
                <td><input type="text" class="form-control form-control-sm currency" name="rates[${rateIndex}][admin_fee]" value="0"></td>
                <td><input type="number" step="0.01" class="form-control form-control-sm" name="rates[${rateIndex}][ppn_percentage]" value="0"></td>
                <td><input type="text" class="form-control form-control-sm" name="rates[${rateIndex}][estimated_days]" placeholder="2-3"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        $('#ratesBody').append(html);
        rateIndex++;
        initCurrencyMask();
        renumberRates();
    }

    function renumberRates() {
        $('#ratesBody tr').each(function(i) {
            $(this).find('.rate-no').text(i + 1);
        });
    }

    // Common
    function removeRow(el) {
        $(el).closest('tr').remove();
        renumberItems();
        renumberPackages();
        renumberRates();
        calculateTotalInvoice();
        calculateTotalWeight();
    }

    // Form submit - convert currency to number
    $('#formEstimate').on('submit', function() {
        $('.currency').each(function() {
            let val = parseNumber($(this).val());
            $(this).val(val);
        });
    });
</script>
