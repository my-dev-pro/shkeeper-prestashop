{if $status}
    <div class="row">
        <div class="col-sm-12">
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
                <input type="button" value="{$entry_request_address}" id="get-address" class="btn btn-danger" style="margin-top: 2vh;" />
            </div>
        </div>
        <div class="col-sm-12 pull-right" style="margin-top: 3vh;">
            <div class="pay-container" hidden>
                <strong>{$entry_address}:</strong>
                <p id="wallet-address"></p>
            </div>
            <div class="pay-container" hidden>
                <strong>{$entry_amount}:</strong>
                <p id="amount"></p>
            </div>
            <div id="qrcode"></div>
            
        </div>
        <div class="col-sm-12" hidden>
            <div id="post-url" hidden>{$wallet_controller}</div>
            <input type="text" id="wallet_address" name="wallet_address" value="" hidden/>
            <input type="text" id="wallet_amount" name="wallet_amount" value="" hidden/>
        </div>
    </div>
{/if}

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>