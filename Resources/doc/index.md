Smarty block to list all available lists on frontend:
----

**Syntax:**

```smarty
{{ list_newsletter }}                   
    ...
{{ /list_newsletter }}
```

**All possible list's constraints and parameters:**
```smarty
{{ list_newsletter length="5" order="created_at desc" constraints="listId is 2e4tgs34e name is testList isEnabled is true" }}
   list_content_here...
{{ /list_newsletter }}
```

**Available block constraints:**

  - listId - MailChimp list id
  - name - MailChimp list name
  - isEnabled - MailChimp list visibility for users

 

**Extra parameters:**

  - length - determines max amount of lists to display
  - order -  default order set to date created - ascending (e.g. order="created_at desc")
  
**Usage example:**
```smarty
{{ list_newsletter order="created_at desc" }}
    {{ $gimme->newsletter_list->created }}
    {{ $gimme->newsletter_list->id }}
    {{ $gimme->newsletter_list->enabled }}
    {{ $gimme->newsletter_list->name }}
    {{ $gimme->newsletter_list->subscribers_count }}
{{ /list_newsletter }}
```

Example frontend subscribe form:
----

To display defined MailChimp lists, we will need a form field to display them and make the possibility for users to subscribe to it:

```html
{{ dynamic }}
<form action="newsletter-plugin/subscribe" method="POST" id="newsletter-subscribe-form" class="subscribe-newsletter-lists">
    <ul>
        <li id="newsletter-boxes" >
            {{ list_newsletter order="created_at desc" }}
            {{if $gimme->newsletter_list->enabled }}
            <dl>
                <input type="hidden" {{ if $gimme->newsletter_list->isSubscribed($user->email, $gimme->newsletter_list->id) }} checked {{/if}} name="newsletter-list-id" value="{{ $gimme->newsletter_list->id }}">
                <input type="hidden" name="groups" value="true">
                <input type="hidden" name="newsletter-type" value="html">
                {{ foreach from=$gimme->newsletter_list->groups item=group}}
                <label for="{{ $group->getName() }}-newsletter">
                <input type="hidden" name="{{ $group->getGroupId() }}[]">
                <input type="checkbox" {{ if $gimme->newsletter_list->isSubscribedToGroup($gimme->newsletter_list->id, $group->getName()) }} checked {{/if}} name="{{ $group->getGroupId() }}[]" value="{{ $group->getName() }}" id="{{ $group->getName() }}-newsletter">
                {{ if $group->getName() == 'group1' }}
                group1 description
                {{ elseif $group->getName() == 'group2' }}
                group2 description
                {{ elseif $group->getName() == 'group3' }}
                group3 description
                {{ /if }}<br>
                </label>
                {{/foreach}}
            </dl>
            {{/if}}
            {{ /list_newsletter }}
        </li>
        <li class="buttons">
            <p id="newsletter-message">
            <p>
                <button type="submit" class="button state_hidden" >Submit</button>
        </li>
    </ul>
</form>
{{ /dynamic }}
```
