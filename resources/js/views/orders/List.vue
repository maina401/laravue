<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        :placeholder="$t('table.keyword')"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-select
        v-model="query.role"
        :placeholder="$t('table.category')"
        clearable
        style="width: 90px"
        class="filter-item"
        @change="handleFilter"
      >
        <el-option v-for="item in orderCategories" :key="item" :label="item | uppercaseFirst" :value="item" />
      </el-select>
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="primary"
        icon="el-icon-plus"
        @click="handleCreate"
      >
        {{ $t('table.add') }}
      </el-button>
      <el-button
        v-waves
        :loading="downloading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >
        {{ $t('table.export') }}
      </el-button>
    </div>

    <el-table v-loading="loading" :data="list" border fit highlight-current-row style="width: 100%">
      <el-table-column align="center" label="ID" width="80">
        <template slot-scope="scope">
          <span>{{ scope.row.index }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Name">
        <template slot-scope="scope">
          <span>{{ scope.row.name }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Email">
        <template slot-scope="scope">
          <span>{{ scope.row.email }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Role" width="120">
        <template slot-scope="scope">
          <span>{{ scope.row.roles.join(', ') }}</span>
        </template>
      </el-table-column>

      <el-table-column align="center" label="Actions" width="350">
        <template slot-scope="scope">
          <router-link v-if="!scope.row.roles.includes('admin')" :to="'/administrator/users/edit/'+scope.row.id">
            <el-button v-permission="['manage user']" type="primary" size="small" icon="el-icon-edit">
              Edit
            </el-button>
          </router-link>
          <el-button
            v-if="!scope.row.roles.includes('admin')"
            v-permission="['manage permission']"
            type="warning"
            size="small"
            icon="el-icon-edit"
            @click="handleEditPermissions(scope.row.id);"
          >
            Permissions
          </el-button>
          <el-button
            v-if="scope.row.roles.includes('visitor')"
            v-permission="['manage user']"
            type="danger"
            size="small"
            icon="el-icon-delete"
            @click="handleDelete(scope.row.id, scope.row.name);"
          >
            Delete
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />

    <el-dialog :visible.sync="dialogPermissionVisible" :title="'Edit Permissions - ' + currentUser.name">
      <div v-if="currentUser.name" v-loading="dialogPermissionLoading" class="form-container">
        <div class="permissions-container">
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Menus">
                <el-tree
                  ref="menuPermissions"
                  :data="normalizedMenuPermissions"
                  :default-checked-keys="permissionKeys(userMenuPermissions)"
                  :props="permissionProps"
                  show-checkbox
                  node-key="id"
                  class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="block">
            <el-form :model="currentUser" label-width="80px" label-position="top">
              <el-form-item label="Permissions">
                <el-tree
                  ref="otherPermissions"
                  :data="normalizedOtherPermissions"
                  :default-checked-keys="permissionKeys(userOtherPermissions)"
                  :props="permissionProps"
                  show-checkbox
                  node-key="id"
                  class="permission-tree"
                />
              </el-form-item>
            </el-form>
          </div>
          <div class="clear-left" />
        </div>
        <div style="text-align:right;">
          <el-button type="danger" @click="dialogPermissionVisible=false">
            {{ $t('permission.cancel') }}
          </el-button>
          <el-button type="primary" @click="confirmPermission">
            {{ $t('permission.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
    <el-dialog :title="'Create new Order'" :visible.sync="dialogFormVisible">
      <div v-loading="orderCreating" class="form-container">
        <el-form
          ref="orderForm"
          :rules="rules"
          :model="newOrder"
          label-position="left"
          label-width="150px"
          style="max-width: 500px;"
          @submit="validateBeforeSubmit"
        >

          <el-form-item :label="$t('order.category')" prop="category">
            <el-select v-model="newOrder.category" class="filter-item" placeholder="Please select Subject">
              <el-option v-for="item in orderCategories" :key="item" :label="item | uppercaseFirst" :value="item" />
            </el-select>
          </el-form-item>
          <el-form-item :label="$t('order.deadline')" prop="date">
            <el-col :span="11">
              <el-date-picker v-model="newOrder.date" type="date" placeholder="Pick a date" style="width: 100%;" />
            </el-col>
            <el-col :span="2" class="line">
              -
            </el-col>
            <el-col :span="11">
              <el-time-picker v-model="newOrder.time" type="fixed-time" placeholder="Pick a time" style="width: 100%;" />
            </el-col>
          </el-form-item>
          <el-form-item :label="$t('order.pages')" prop="pages">
            <el-col :span="6">
              <a @click="decrement()">&mdash;</a>
            </el-col>
            <el-col :span="6" class="line">
              <el-input v-model="newOrder.pages" type="number" style="width: 100%;" />
            </el-col>
            <el-col :span="6">
              <a @click="increment()">&#xff0b;</a>
            </el-col>
          </el-form-item>
          <el-form-item :label="$t('order.title')" prop="title">
            <el-input v-model="newOrder.title" />
          </el-form-item>
          <el-form-item :label="$t('user.email')" prop="email">
            <el-input v-model="newOrder.email" />
          </el-form-item>
          <el-form-item :label="$t('order.description')" prop="description">
            <div>
              <el-col :span="600">
                <tinymce v-model="newOrder.description" :height="300" />
              </el-col>
            </div>
          </el-form-item>
          <el-form-item :label="$t('order.attachments')" prop="attachments">
            <div class="editor-container">
              <dropzone
                id="myVueDropzone"
                v-model="newOrder.attachment"
                url="/api/files/orders/attach"
                @dropzone-removedFile="dropzoneR"
                @dropzone-success="dropzoneS"
              />
              <small>Up to 5 files. If more, compress to zip and upload</small>
            </div>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="dialogFormVisible = false">
            {{ $t('table.cancel') }}
          </el-button>
          <el-button type="primary" @click="createOrder()">
            {{ $t('table.confirm') }}
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<style scoped>
</style>
<script>
import Pagination from '@/components/Pagination'; // Secondary package based on el-pagination
import UserResource from '@/api/user';
import OrderResource from '@/api/orders';
import Resource from '@/api/resource';
import waves from '@/directive/waves'; // Waves directive
import permission from '@/directive/permission'; // Permission directive
import checkPermission from '@/utils/permission'; // Permission checking
import Tinymce from '@/components/Tinymce'; // Import Tiny Editor
import Dropzone from '@/components/Dropzone';// for the drG nd drop

const userResource = new UserResource();
const orderResource = new OrderResource();
const permissionResource = new Resource('permissions');

export default {
  name: 'Orders',
  components: { Pagination, Tinymce, Dropzone },
  directives: { waves, permission },
  data() {
    return {
      content: '',
      list: null,
      total: 0,
      loading: true,
      downloading: false,
      orderCreating: false,
      quantity: 1,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
      orderCategories: ['management', 'psychology', 'Comp Science'],
      newOrder: {},
      dialogFormVisible: false,
      dialogPermissionVisible: false,
      dialogPermissionLoading: false,
      currentUserId: 0,
      currentUser: {
        name: '',
        permissions: [],
        rolePermissions: [],
      },
      rules: {
        category: [{ required: true, message: 'Category is required', trigger: 'blur' }],
        title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
        date: [{ required: true, message: 'Date is required', trigger: 'blur' }],
        description: [
          {
            required: true,
            message: 'A description is required. It will help writers understand it more.',
            trigger: 'blur',
          },
          { length: 100, message: 'Enter a description that is at least 100 characters', trigger: 'blur' },
        ],
        pages: [
          {
            required: true,
            message: 'Select the number of pages',
            trigger: 'blur',
          },
          { min: 1, message: 'Pages have to be more than one', trigger: 'blur' },
        ],
      },
      permissionProps: {
        children: 'children',
        label: 'name',
        disabled: 'disabled',
      },
      permissions: [],
      menuPermissions: [],
      otherPermissions: [],
    };
  },
  computed: {
    normalizedMenuPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1, // Just a faked ID
        disabled: true,
        children: this.classifyPermissions(tmp).menu,
      };

      tmp = this.menuPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0, // Faked ID
        name: 'Extra menus',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    normalizedOtherPermissions() {
      let tmp = [];
      this.currentUser.permissions.role.forEach(permission => {
        tmp.push({
          id: permission.id,
          name: permission.name,
          disabled: true,
        });
      });
      const rolePermissions = {
        id: -1,
        name: 'Inherited from role',
        disabled: true,
        children: this.classifyPermissions(tmp).other,
      };

      tmp = this.otherPermissions.filter(permission => !this.currentUser.permissions.role.find(p => p.id === permission.id));
      const userPermissions = {
        id: 0,
        name: 'Extra permissions',
        children: tmp,
        disabled: tmp.length === 0,
      };

      return [rolePermissions, userPermissions];
    },
    userMenuPermissions() {
      return this.classifyPermissions(this.userPermissions).menu;
    },
    userOtherPermissions() {
      return this.classifyPermissions(this.userPermissions).other;
    },
    userPermissions() {
      return this.currentUser.permissions.role.concat(this.currentUser.permissions.user);
    },
  },
  created() {
    this.resetNewOrder();
    this.getList();
    if (checkPermission(['manage permission'])) {
      this.getPermissions();
    }
  },
  methods: {
    checkPermission,
    async getPermissions() {
      const { data } = await permissionResource.list({});
      const { all, menu, other } = this.classifyPermissions(data);
      this.permissions = all;
      this.menuPermissions = menu;
      this.otherPermissions = other;
    },
    validateBeforeSubmit(e) {
      if (this.errors.any()) {
        // Prevent the form from submitting
        e.preventDefault();
      }
    },

    async getList() {
      const { limit, page } = this.query;
      this.loading = true;
      const { data, meta } = await userResource.list(this.query);
      this.list = data;
      this.list.forEach((element, index) => {
        element['index'] = (page - 1) * limit + index + 1;
      });
      this.total = meta.total;
      this.loading = false;
    },
    handleFilter() {
      this.query.page = 1;
      this.getList();
    },
    handleCreate() {
      this.resetNewOrder();
      this.dialogFormVisible = true;
      this.$nextTick(() => {
        this.$refs['orderForm'].clearValidate();
      });
    },
    increment() {
      this.pages++;
    },
    decrement() {
      if (this.pages === 1) {
        alert('Negative quantity not allowed');
      } else {
        this.pages--;
      }
    },
    handleDelete(id, name) {
      this.$confirm('This will permanently delete user ' + name + '. Continue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(() => {
        userResource.destroy(id).then(response => {
          this.$message({
            type: 'success',
            message: 'Delete completed',
          });
          this.handleFilter();
        }).catch(error => {
          console.log(error);
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete canceled',
        });
      });
    },
    async handleEditPermissions(id) {
      this.currentUserId = id;
      this.dialogPermissionLoading = true;
      this.dialogPermissionVisible = true;
      const found = this.list.find(user => user.id === id);
      const { data } = await userResource.permissions(id);
      this.currentUser = {
        id: found.id,
        name: found.name,
        permissions: data,
      };
      this.dialogPermissionLoading = false;
      this.$nextTick(() => {
        this.$refs.menuPermissions.setCheckedKeys(this.permissionKeys(this.userMenuPermissions));
        this.$refs.otherPermissions.setCheckedKeys(this.permissionKeys(this.userOtherPermissions));
      });
    },
    createOrder() {
      this.$refs['orderForm'].validate((valid) => {
        if (valid) {
          this.orderCreating = true;
          orderResource
            .store(this.newOrder)
            .then(response => {
              this.$message({
                message: `New order ${this.newOrder.title} (${this.newOrder.category}) has been created successfully.`,
                type: 'success',
                duration: 5 * 1000,
              });
              this.resetNewOrder();
              this.dialogFormVisible = false;
              this.handleFilter();
            })
            .catch(error => {
              console.log(error);
            })
            .finally(() => {
              this.orderCreating = false;
            });
        }
      });
    },
    resetNewOrder() {
      this.newOrder = {
        title: '',
        date: '',
        time: '',
        description: '',
        pages: '',
        amount: '',
        instructions: '',
        attachment: [],
        category: '',
        level: '',
      };
    },
    handleDownload() {
      this.downloading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'order_id', 'title', 'amount', 'category', 'level', 'pages', 'date', 'time'];
        const filterVal = ['index', 'id', 'title', 'amount', 'category', 'level', 'pages', 'date', 'time'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'order-list',
        });
        this.downloading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => v[j]));
    },
    permissionKeys(permissions) {
      return permissions.map(permssion => permssion.id);
    },
    classifyPermissions(permissions) {
      const all = [];
      const menu = [];
      const other = [];
      permissions.forEach(permission => {
        const permissionName = permission.name;
        all.push(permission);
        if (permissionName.startsWith('view menu')) {
          menu.push(this.normalizeMenuPermission(permission));
        } else {
          other.push(this.normalizePermission(permission));
        }
      });
      return { all, menu, other };
    },

    normalizeMenuPermission(permission) {
      return {
        id: permission.id,
        name: this.$options.filters.uppercaseFirst(permission.name.substring(10)),
        disabled: permission.disabled || false,
      };
    },

    normalizePermission(permission) {
      const disabled = permission.disabled || permission.name === 'manage permission';
      return { id: permission.id, name: this.$options.filters.uppercaseFirst(permission.name), disabled: disabled };
    },

    confirmPermission() {
      const checkedMenu = this.$refs.menuPermissions.getCheckedKeys();
      const checkedOther = this.$refs.otherPermissions.getCheckedKeys();
      const checkedPermissions = checkedMenu.concat(checkedOther);
      this.dialogPermissionLoading = true;

      userResource.updatePermission(this.currentUserId, { permissions: checkedPermissions }).then(response => {
        this.$message({
          message: 'Permissions has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
        this.dialogPermissionLoading = false;
        this.dialogPermissionVisible = false;
      });
    },
    dropzoneS(file, ele, response) {
      const Obj = { 'filename': file.upload.filename,
        'filepath': response.path };
      this.newOrder.attachment.push(Obj);
      console.log(this.newOrder.attachment);
      this.$message({ message: 'Upload success', type: 'success' });
    },
    dropzoneR(file) {
      let counter = 0;
      const self = this;
      this.newOrder.attachment.forEach(function(item) {
        if (item.filename === file.upload.filename){
          console.log(`Removing file ${item.filename}`);
          self.newOrder.attachment.splice(counter, 1);
          counter += 1;
        }
      });
      this.$message({ message: 'Delete success', type: 'success' });
    },
  },
};
</script>

<style lang="scss" scoped>
.edit-input {
  padding-right: 100px;
}

.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

.dialog-footer {
  text-align: left;
  padding-top: 0;
  margin-left: 150px;
}

.app-container {
  flex: 1;
  justify-content: space-between;
  font-size: 14px;
  padding-right: 8px;

  .block {
    float: left;
    min-width: 250px;
  }

  .clear-left {
    clear: left;
  }
}

</style>
