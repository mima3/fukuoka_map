var teapot = (function() {
  var END_POINT_PREFIXES = 'https://teapot-api.bodic.org/api/teapot/prefixes';
  var END_POINT_SPARQL = 'https://teapot-api.bodic.org/api/v1/sparql';
  var prefixes = {
    'geo': 'http://www.w3.org/2003/01/geo/wgs84_pos#',
    'rdf': 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
    'rdfs': 'http://www.w3.org/2000/01/rdf-schema#',
    'xsd': 'http://www.w3.org/2001/XMLSchema#',
    'schema': 'http://schema.org/',
    'foaf': 'http://xmlns.com/foaf/0.1/',
    'tpp': 'http://teapot.bodic.org/predicate/',
    'tpf': 'http://teapot.bodic.org/facility/',
    'tpe': 'http://teapot.bodic.org/equipement/',
    'tpo': 'http://teapot.bodic.org/organization/',
    'tpt': 'http://teapot.bodic.org/type/',
    'tpd': 'http://teapot.bodic.org/dataset/',
    'tps': 'http://teapot.bodic.org/stats/'
  };
  /**
   * WHERE区、UNION区の構築用のオブジェクト
   */
  var Where = (function() {
    var inst = function(parent) {
      this._parent = parent;
      this._lstWhere = [];
      this._lstFilter = [];
    };

    var p = inst.prototype;

    p.where = function(subject, predicate, obj) {
      var s = util.format('%s %s %s .', subject, predicate, obj);
      this._lstWhere.push(s);
      return this;
    };

    p.filter = function(condition) {
      var s = util.format(' FILTER (%s) ', condition);
      this._lstFilter.push(s);
      return this;
    };

    p.filterRegex = function(col, match, option) {
      var s = util.format(' FILTER regex(%s,"%s","%s") ', col, match, option);
      this._lstFilter.push(s);
      return this;
    };

    p.execute = function(callback) {
      return this._parent.execute(callback);
    };

    p.limit = function(d) {
      return this._parent.limit(d);
    };

    p.offset = function(d) {
      return this._parent.offset(d);
    };

    p.orderby = function(d) {
      return this._parent.orderby(d);
    };

    p.union = function() {
      return this._parent.union();
    };

    p.sql = function() {
      var s = '{';
      for (var i = 0; i < this._lstWhere.length; ++i) {
        s += this._lstWhere[i];
      }
      for (var i = 0; i < this._lstFilter.length; ++i) {
        s += this._lstFilter[i];
      }
      s += '}';
      return s;
    };

    return inst;
  })();


  /**
   * SPARQLのクエリー構築用のオブジェクト
   */
  var Query = (function() {
    function _reset(inst) {
      inst._strCol = '*';
      inst._lstWhere = [];
      inst._limit = undefined;
      inst._distinct = false;
      inst._offset = undefined;
      inst._orderby = undefined;
    }
    var inst = function() {
      _reset(this);
    };
    var p = inst.prototype;

    p.columns = function(cols) {
      if (!cols) {
        this._strCol = '*';
        return this;
      }
      this._strCol = cols.join(' ');
      return this;
    };

    p.where = function(subject, predicate, obj) {
      if (this._lstWhere.length == 0) {
        this._lstWhere.push(new Where(this));
      }
      return this._lstWhere[0].where(subject, predicate, obj);
    };

    p.union = function() {
      var obj = new Where(this);
      this._lstWhere.push(obj);
      return obj;
    };

    p.limit = function(d) {
      this._limit = d;
      return this;
    };

    p.offset = function(d) {
      this._offset = d;
      return this;
    };

    p.orderby = function(d) {
      this._orderby = d;
      return this;
    };

    p.distinct = function() {
      this._distinct = true;
      return this;
    };
    p.sql = function() {
      var sql = '';
      for (var key in prefixes) {
        sql += util.format('PREFIX %s: <%s>\n', key, prefixes[key]);
      }
      if (inst._distinct) {
        sql += util.format('SELECT DISTINCT %s ', this._strCol);
      } else {
        sql += util.format('SELECT %s ', this._strCol);
      }
      for (var i = 0; i < this._lstWhere.length; ++i) {
        if (i == 0) {
          sql += ' WHERE {';
        } else {
          sql += ' UNION ';
        }
        sql += this._lstWhere[i].sql();
      }
      sql += '}';
      if (typeof this._orderby !== 'undefined') {
        sql += util.format(' ORDER BY %s ', this._orderby);
      }
      if (typeof this._limit !== 'undefined') {
        sql += util.format(' LIMIT %d ', this._limit);
      }
      if (typeof this._offset !== 'undefined') {
        sql += util.format(' OFFSET %d ', this._offset);
      }
      return sql;
    }

    function postSprql(sql, callback) {
      $.post(
        END_POINT_SPARQL,
        {
          query: sql
        },
        function(res) {
          callback(null, res);
        },
        'json'
      ).error(function(e) {
         callback(e.responseText, null);
      });
    }

    p.execute = function(callback) {
      var sql = this.sql();
      _reset(this);
      postSprql(sql, callback);
    };

    p.executeSpilit = function(limit, callback) {
      this.limit(limit);
      var binding = [];
      var cnt = 0;
      var self = this;
      function nestPost() {
        self.offset(limit * cnt);
        var sql = self.sql();
        postSprql(sql, function(err, res) {
          if (err) {
            _reset(this);
            callback(err);
            return;
          }
          if (res.results.bindings.length == 0) {
            _reset(this);
            res.results.bindings = res.results.bindings.concat(binding);
            callback(null, res);
            return;
          }
          binding = binding.concat(res.results.bindings);
          ++cnt;
          nestPost();
        });
      }
      nestPost();
    };
    return inst;
  })();

  /**
   * prefixes の取得コマンド
   */
  function _runPrefixes(callback) {
    $.get(
      END_POINT_PREFIXES,
      {},
      function(res) {
        if (res['@status'] != 'OK') {
          callback(res['@status'], null);
          return;
        }
        callback(null, prefixes);
      },
      'json'
    ).error(function(e) {
       callback(e.responseText, null);
    });
  }

  function _buildClassesTree(callback) {
    var query = new Query();
    query
      .distinct()
      .columns(['?s', '?p', '?o'])
      .where('?s', '<http://www.w3.org/1999/02/22-rdf-syntax-ns#type>', '<http://www.w3.org/2000/01/rdf-schema#class>')
      .where('?s', '?p', '?o')
      .orderby('?s')
      .execute(function(err, res) {
        if (err) {
          callback(err, null);
          return;
        }
        var classTree = {
          classes: {},
          tree: {
            'http://teapot.bodic.org/type/rootobject' : {}
          }
        };
        var bind = res.results.bindings;
        for (var i = 0; i < bind.length; ++i) {
          if (!classTree.classes[bind[i].s.value]) {
            classTree.classes[bind[i].s.value] = {};
          }
          classTree.classes[bind[i].s.value][bind[i].p.value] = bind[i].o.value;
        }
        function buildTree(chkKey, node) {
          for (var c in classTree.classes) {
            if (classTree.classes[c]['http://www.w3.org/2000/01/rdf-schema#subClassOf'] == chkKey) {
              node[c] = {};
              buildTree(c, node[c]);
            }
          }
        }
        buildTree('http://teapot.bodic.org/type/rootobject', classTree.tree['http://teapot.bodic.org/type/rootobject']);
        callback(null, classTree);
      });
  }


  function _convertObject(res, defaultobj, hierarchy) {
    var recs = res.results.bindings;
    var ret = defaultobj;
    if (!ret) {
      ret = {};
    }
    if (!hierarchy) {
      hierarchy = ['s', 'p', 'o'];
    }
    for (var i = 0; i < recs.length; ++i) {
      for (var j = 0; j < hierarchy.length; ++j) {
        var tmp = ret;
        for (var j = 0; j < hierarchy.length - 1; ++j) {
          var k = recs[i][hierarchy[j]].value;
          if (j != hierarchy.length - 2) {
            if (!tmp[k]) {
                tmp[k] = {};
            }
            tmp = tmp[k];
          } else {
            var v = {
              value: recs[i][hierarchy[j + 1]].value,
              translate_value: recs[i][hierarchy[j + 1]].translate_value
            };

            if (tmp[k]) {
              // 末尾の場合
              if (!Array.isArray(tmp[k])) {
                var p = tmp[k];
                tmp[k] = [];
                tmp[k].push(p);
              }
              tmp[k].push(v);
            } else {
              tmp[k] = v;
            }
          }
        }
      }
    }
    return ret;
  }

  return {
    runPrefixes: _runPrefixes,
    buildClassesTree: _buildClassesTree,
    Query: Query,
    convertObject: _convertObject
  };
})();
