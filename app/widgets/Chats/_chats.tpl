{loop="$chats"}
    {if="$emptyItems"}
        {autoescape="off"}
            {$c->prepareEmptyChat($key)}
        {/autoescape}
    {else}
        {autoescape="off"}
            {$c->prepareChat($key)}
        {/autoescape}
    {/if}
{/loop}
