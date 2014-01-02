<script type="text/javascript"
        src="{$smarty.const.WWW_TOP}/themes/{$site->style}/scripts/multi-ops.js"></script>

<div class="row">

        {$pager}

    <div class="pull-right">
                    With Selected: <button id="btnMultiNzbDownload" class="btn btn-info btn-mini nzb_multi_operations_download">Download NZBs</button>
                    <button id="btnMultiCartAdd" class="btn btn-info btn-mini">Add to Cart</button>
                    {if $sabintegrated}
                        <button id="btnMultiSendToSab" type="button" class="btn btn-success btn-mini nzb_multi_operations_sab">Send to SAB</button>
                    {/if}
    </div>
</div>
