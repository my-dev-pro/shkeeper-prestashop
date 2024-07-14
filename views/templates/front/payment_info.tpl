{if $status}
    <div class="row">
        <div class="col-sm-6">
            <div class="payment-container">
                {if $instructions}
                    <p>{$instructions}</p>
                {/if}

                <div class="crypto-icons">
                    <select id="shkeeper-currency" name="shkeeper-currency">
                    {foreach $currencies as $currency}
                        <option value="{$currency.name}">{$currency.display_name}</option>
                    {/foreach}
                    </select>
                </div>
                <input type="button" value="{$get_address}" id="get-address" class="btn btn-danger" style="margin-top: 2vh;" />
            </div>
        </div>
        <div class="col-sm-6 pull-right">
            <p id="wallet-address"></p>
            <p id="amount"></p>
            <div id="qrcode"></div>
        </div>
  </div>
{/if}

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>