<?php


?>

<table 
    class="variations field" 
    data-jgbsbsc-field-type="additional-selection" 
    cellspacing="0" 
    data-field-additional-selection-id="{{additional-selection-id}}"
    data-field-additional-selection-slug="{{additional-selection-slug}}"
>
    <tbody>
        <tr>
        <td colspan="2" class="label">{{additional-selection-label}} </td>
        </tr>
        
        <tr>
            <td class="value">
                <div class="wrapper">
                    <label for="id-value-{{opt-slug}}">{{opt-label}}</label>
                    <input type="radio" name="{{opt-slug}}" value="{{opt-value}}" id="id-value-{{opt-slug}}"/>
                    
                    <div class="buton-group simple" data-opts-sels="">

                        {{#additional-selection-options}}

                    </div>

                </div>
            </td>
        </tr>

    </tbody>
</table>