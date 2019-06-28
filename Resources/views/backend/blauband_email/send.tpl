{extends file="parent:backend/blauband_email/send.tpl"}

{block name="mailContentWrapperAdditional"}
    {$smarty.block.parent}
    <div class="two-cols">
        <label>{s namespace="blauband/mail" name="template"}Template{/s}</label>
        <select name="template">
            {foreach $templates as $template}
                <option value="{$template.id}">{$template.name|escape}</option>
            {/foreach}
        </select>
    </div>
{/block}