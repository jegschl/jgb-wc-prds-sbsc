<?php


?>

<table 
    class="variations field" 
    data-jgbsbsc-field-type="additional-selection" 
    cellspacing="0" 
    data-field-addtiopnal-selection-id="{{additional-selection-id}}"
>
    <tbody>
        <tr>
        <td colspan="2" class="label">{{additional-selection-label}} </td>
        </tr>
        
        <tr>
            <td class="value" data-reg-val-id="{{opt-id}}">
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