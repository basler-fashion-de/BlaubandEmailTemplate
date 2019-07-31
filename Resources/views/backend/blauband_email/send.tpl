{extends file="parent:backend/blauband_email/send.tpl"}

{block name="header"}
    {$smarty.block.parent}

    <link rel="stylesheet" href="{link file="backend/_public/src/css/email-template.css"}">
{/block}

{block name="mailContentWrapperAdditional"}
    {$smarty.block.parent}
    <div class="two-cols">
        <label>{s namespace="blauband/mail" name="template"}Template{/s}</label>
        <select name="template">
            {foreach $templates as $template}
                <option value="{$template.id}">{$template.name|escape}</option>
            {/foreach}
        </select>
        {if empty($orderId)}
            <div class="blauband--notes">
                {s namespace="blauband/mail" name="noOrderData"}{/s}
            </div>
        {/if}
    </div>
{/block}