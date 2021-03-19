(function(){
    function addField () {
      const columnInput = document.getElementById('new_column_name');
      let column = columnInput.value.trim();
      if (!column) {
        alert('Please enter a column name!');
        return false;
      }
      column = column.replace(/[^\w\s_]/, '');
      column = column.replace(/\s+/, '_');
      const numericCheck = document.getElementById('new_is_numeric');
      const postData = new FormData();
      postData.append('request_type', 'add_field');
      postData.append('column_name', column);
      postData.append('is_numeric', numericCheck.checked);
      fetch(fetchUrl, {
        method: 'post',
        body: postData
      })
      .then(response => response.json())
      .then(response => {
        alert(response.message || 'There was an error adding this field.');
        if (response.success === true) {
          console.log(response);
          document.getElementById('info').innerHTML = 'Reloading...';
          setFieldList();
          columnInput.value = null;
          numericCheck.checked = false;
        }
      });
    }
  
    function getFetchUrl() {
      let url = window.location.href.split('/');
      url.pop();
      url.pop();
      url = url.join('/') + '/index.php';
      return url;
    }
  
    function setFieldList () {
      const postData = new FormData();
      postData.append('request_type', 'get_field_list');
      fetch(fetchUrl, {
        method: 'post',
        body: postData
      })
      .then(response => response.json())
      .then(response => showData(response));
    }
  
    function showData (json) {
      const infoDiv = document.getElementById('info');
      if (!json.success) {      
          infoDiv.innerHTML = json.message || 'Error';
      }
      const list = document.createElement('ul');
      const data = json.data;
      console.log(data);
      for (let i = 0; i < data.length; i++) {
        const item = document.createElement('li');
        let html = data[i].columnName;
        if (data[i].isNumeric) {
          html += ' - <em>numeric</em> ';
        }
        item.innerHTML = html;
        list.appendChild(item);
      }
      infoDiv.innerHTML = null;
      infoDiv.appendChild(list);
    }
  
    const fetchUrl = getFetchUrl();
    setFieldList();
    document.getElementById('add-field').addEventListener('click', addField);
  })();