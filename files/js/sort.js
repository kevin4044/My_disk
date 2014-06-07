/**
 * Created by wangjunlong on 14-5-4.
 */

MYsort ={
    data_container:'',
    sort:function (key_class, order) {
        var $data_container = $(this.data_container);
        var $each = $data_container.children();
        var dom_array = new Array();

        $each.each(function () {
            var obj = new Object();
            obj.key = $(this).find(key_class).prop('title');
            obj.content = $(this)[0].outerHTML;

            //todo:没有考虑属性名重复的情况
            dom_array.push(obj);
        })
        dom_array.sort(function(a, b){
            if (isNaN(parseInt(a.key)) === false
                && isNaN(parseInt(b.key)) === false) {
                if (order = 'asc') {
                    return parseInt(b.key)-parseInt(a.key);
                } else {
                    return parseInt(a.key)-parseInt(b.key);
                }

            } else {
                if (order = 'asc') {
                    return b.key.localeCompare(a.key);
                } else {
                    return a.key.localeCompare(b.key);
                }
            }
        });

        $data_container.html("");
        for (key in dom_array) {
            $data_container.append(dom_array[key].content);
        }
    }

}
