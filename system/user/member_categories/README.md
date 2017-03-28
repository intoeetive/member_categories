# Member categories

The obvious limitation of ExpressioEngine membership system is that a member can be part of only one membership group. With Member categories, you can assign multiple categories to your members. Categories used are the channel categories, so you can easily relate "groups" of members to "groups" of entries.

Features:
 - Display members belonging to category
 - Display categories assigned to member
 - Check whether a member belongs to certain category
 - Display entries from member's category
 - Assign categories on backend and frontend

## Settings

Before using Member categories, you should select in module settings what category groups you want to be applicable for members. You can also optionally choose to automatically assign parent category for member, if the child category is assigned. 

Note that if you're using MSM you'll have to provide settings for each site separately.

## Usage

### `{exp:member_categories:members}`

Display members belonging to category

#### Example Usage

```
Members in this category:
    {exp:member_categories:members category_url_title="{segment_3}" backspace="1"}
        &lt;a href="{path=profile/{username}}"&gt;{screen_name}&lt;/a&gt;, 
        {if no_results}none{/if}
        {paginate}{pagination_links}{/paginate}
    {/exp:member_categories:members}
```

#### Parameters

- `category_id` - the ID of category to get members
- `category_url_title` - URL title of category
- `custom_fields="yes"` - if set to "yes", will also fetch all custom profile fields data for each member
- `backspace` - number of character to be removed from the end of tag contents on last iteration
- `errors="off"` - if specified, all error messages will be suppressed
- `limit` - number of records per page (omit to disable pagination)
- `order_by` - field to order by. Any field from members database table can be used. Defaults to member_id
- `sort` - sorting direction (asc, desc)
- `paginate="top"` - defines where pagination links should be displayed. Possible values: 'top', 'bottom', 'both' (defaults to bottom)

You MUST provide either category_id OR category_url_title



#### Variables

All variables have the same meaning that in member profile tag.

- `{member_id}`
- `{username}`
- `{screen_name}`
- `{email}`
- `{url}`
- `{location}`
- `{occupation}`
- `{interests}`
- `{aol_im}`
- `{yahoo_im}`
- `{msn_im}`
- `{icq}`
- `{bio}`
- `{signature}`
- `{ip_address}`
- `{birthday}`
- `{avatar_url}`
- `{avatar_width}`
- `{avatar_height}`
- `{photo_url}`
- `{photo_image_width}`
- `{photo_image_height}`
- `{signature_image_url}`
- `{signature_image_width}`
- `{signature_image_height}`
- `{join_date format="%Y-%m-%d"}`
- `{last_visit format="%Y-%m-%d"}`
- `{last_activity format="%Y-%m-%d"}`
- `{my_custom_profile_field}` - custom profile fields are also available if you set parameter custom_fields="yes"
- `{total_results}` - overall number of categories to be displayed
- `{count}` - counter for each iteration (resets on every page)
- `{absolute_count}` - counter for each iteration (continuos thoughout pages)
- `{{if no_results}...{/if}}`  - text to display if there are no results
- `{{paginate}{pagination_links}{/paginate}}`  - pagination links



### `{exp:member_categories:categories}`


Display categories assigned to member

#### Example Usage
```
This member belongs to categories:
{exp:member_categories:categories username="{segment_3}" backspace="1"}
&lt;a href="{path=category/{category_url_title}}"&gt;{category_name}&lt;/a&gt;, 
{if no_results}none{/if}
{/exp:member_categories:categories}
```

#### Parameters

- `{member_id}` - the ID of member to get categories. Defaults to logged in member ID.
- `{username}` - ... or username
- `{custom_fields="yes"}` - if set to "yes", will also fetch all custom profile fields data for each member
- `{category_group}` - you can restrict output to display only categories from certain groups. Provide one or several group IDs (seperate multiple values with a pipe, ex. category_group="2|3|12")
- `{backspace}` - number of character to be removed from the end of tag contents on last iteration
- `{order_by="order"}` - sort categories by category order. If omited, they will be sorted by category name
- `{sort_by_tree="no"}` - if specified, sorting will not respect category group and parents
- `{sort}` - sorting direction (asc, desc)
- `{errors="off"}` - if specified, all error messages will be suppressed

#### Variables 

All variables have the same meaning that in channel categories tag

- `{category_id}`
- `{category_name}`
- `{category_url_title}`
- `{category_image}`
- `{category_description}`
- `{parent_id}`
- `{category_group}` - ID of category group
- `{my_custom_profile_field}` - custom profile fields are also available if you set parameter custom_fields="yes"
- `{total_results}` - overall number of categories to be displayed
- `{count}` - counter for each iteration
- `{{if no_results}...{/if}}`  - text to display if there are no results


### `{exp:member_categories:check}`

Check whether a member belongs to certain category

#### Example Usage
```
{exp:member_categories:check username="{segment_3}" category_id="1"}
The member belongs to the category
{if no_results}The member does NOT belong to the category{/if}
{/exp:member_categories:check}
```

#### Parameters

- `{member_id}` - the ID of member to check.  Defaults to logged in member ID.
- `{username}` - ... or username
- `{category_id}` - the ID of category to check
- `{category_url_title}` - ... or URL tite
- `{errors="off"}` - if specified, all error messages will be suppressed

You MUST provide category_id (or category_url_title)

You can also pass multiple category IDs to check whether member belong to at least one category. Use pipe (|) separator for category_id parameter. E.g. *category_id="1|7|12"*
The contents of tag pair will be returned if there is a match between member and category. Otherwise the contents of {if no_results} block will be returned. No additional variables are available.





### Display entries from member's category

To display entries from the same category that member belongs to, you can use combination of `{exp:member_categories:categories}` and `{exp:channel:entries}` tags.

#### Example Usage
```
{exp:member_categories:categories username="{segment_3}"}
Category: {category_name}, entries:
{exp:channel:entries category="{category_id}" dynamic="no" backspace="1"}
&lt;a href="{path=view/{url_title}}"&gt;{title}&lt;/a&lt;, 
{if no_results}none{/if}
{/exp:channel:entries}
{if no_results}none{/if}
{/exp:member_categories:categories}
```


### Assign categories on backend and frontend

From the module control panel, you can assign any number of categories to member by checking the appropriate checkboxes. Just like you do with categories for entries.
Note that if you're using MSM only category groups for current site are displayed (and only those defined in settings).

To assign categories from the front-end, place `{exp:member_categories:form}` tag pair somewhere in your templates. The tag pair has only one variable &mdash; `{categories}`, which return pre-formatted list of categories checkboxes. If you want to edit the way categories are displayed, you need to edit files in `member_categories/View/frontend` folder.

#### Example Usage
```
My categories:
{exp:member_categories:form}
{categories}
&lt;input type="submit" value="Save" /&gt;
{/exp:member_categories:form}
```





## Changelog

### 3.0.0

- Rewrite for ExpressionEngine 3.0

## License

The purchase of the add-on grants you to use it on single production installation of ExpressionEngine. Should you be willing to use it on several production websites, you should purchase additional licenses. The full license agreement is available [here](http://www.intoeetive.com/docs/license.html)

## Support

Should you have any questions, please email [support@intoeetive.com](mailto:support@intoeetive.com) (for official support) or ask questions on [EE StackOverflow](http://expressionengine.stackexchange.com/) (for community support)
